<?php

namespace Growfund\Services;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\Status\PledgeStatus;
use Growfund\Constants\Tables;
use Growfund\Constants\UserDeleteType;
use Growfund\Supports\Paginator;
use Growfund\Supports\UserMeta;
use Growfund\Constants\UserTypes\Backer;
use Growfund\Constants\WP;
use Growfund\DTO\Backer\BackerDTO;
use Growfund\DTO\Backer\BackerOverviewDTO;
use Growfund\Http\Response;
use Growfund\QueryBuilder;
use Growfund\Supports\Arr;
use Growfund\Supports\Pagination as PaginationSupport;
use Growfund\Supports\User as UserSupport;
use Exception;
use WP_User_Query;

class BackerService extends UserService
{
    protected $pledge_service;
    public function __construct()
    {
        $this->pledge_service = new PledgeService();
    }

    /**
     * Get paginated list of backers.
     *
     * @param array $params Associative array containing:
     *   - int    'limit'       Number of results per page.
     *   - int    'page'        Current page number.
     *   - string 'search'      Search keyword (by ID).
     *   - string 'date_from'   Start date for registration filter (Y-m-d).
     *   - string 'date_to'     End date for registration filter (Y-m-d).
     *
     * @return array Structured response containing:
     *   - array  'results'     Formatted user data.
     *   - int    'total'       Total number of matching users.
     *   - int    'count'       Number of users on this page.
     *   - int    'per_page'    Pagination limit.
     *   - int    'current_page' Current page number.
     *   - bool   'has_more'    Whether there are more pages.
     */
    public function paginated(array $params)
    {
        $users_table = QueryBuilder::prefix(WP::USERS_TABLE);

        $limit = isset($params['limit']) ? (int) $params['limit'] : 10;
        $page = isset($params['page']) ? (int) $params['page'] : 1;
        $orderby = isset($params['orderby']) ? $params['orderby'] : 'ID';
        $order = isset($params['order']) ? $params['order'] : 'DESC';
        $search = !empty($params['search']) ? $params['search'] : '';
        $status = !empty($params['status']) ? $params['status'] : 'all';

        $query_args = [
            'count_total'    => true,
            'number'         => $limit,
            'paged'          => $page,
            'orderby'        => $orderby,
            'order'          => strtoupper($order),
            'role'           => Backer::ROLE,
        ];

        if (!empty($search)) {
            $query_args['search'] = '*' . $search . '*';
            $query_args['search_columns'] = [
                "{$users_table}.ID",
                "{$users_table}.user_login",
                "{$users_table}.user_email",
                "{$users_table}.user_nicename",
                "{$users_table}.display_name"
            ];
        }

        // Add date_query for user_registered filtering
        if (!empty($params['start_date']) || !empty($params['end_date'])) {
            $date_query = [];

            if (!empty($params['start_date'])) {
                $date_query['after'] = $params['start_date'];
            }

            if (!empty($params['end_date'])) {
                $date_query['before'] = $params['end_date'];
            }

            $date_query['inclusive'] = true;
            $query_args['date_query'] = [$date_query];
        }

        $campaign_filter_callback = $this->apply_campaign_filter((int) $params['campaign_id'], QueryBuilder::prefix(Tables::PLEDGES));

        if ($this->has_campaign_id($params)) {
            add_filter('pre_user_query', $campaign_filter_callback);
        }

        $fundraiser_filter_callback = $this->apply_fundraiser_filter(QueryBuilder::prefix(Tables::PLEDGES));

        if (growfund_user()->is_fundraiser()) {
            add_filter('pre_user_query', $fundraiser_filter_callback);
        }

        if ($status === 'all') {
            $query_args['meta_query'][] = [
                'relation' => 'OR',
                [
                    'key'     => growfund_with_prefix(UserSupport::SOFT_DELETE_KEY),
                    'compare' => 'NOT EXISTS',
                ],
                [
                    'key'     => growfund_with_prefix(UserSupport::SOFT_DELETE_KEY),
                    'value'   => '1',
                    'compare' => '!=',
                ],
            ];

            $query_args['meta_query'][] = [
                'relation' => 'OR',
                [
                    'key'     => growfund_with_prefix(UserSupport::IS_ANONYMIZED),
                    'compare' => 'NOT EXISTS',
                ],
                [
                    'key'     => growfund_with_prefix(UserSupport::IS_ANONYMIZED),
                    'value'   => '1',
                    'compare' => '!=',
                ],
            ];
        } elseif ($status === 'trashed') {
            $query_args['meta_query'][] = [
                [
                    'key'     => growfund_with_prefix(UserSupport::SOFT_DELETE_KEY),
                    'value'   => '1',
                ],
            ];

            $query_args['meta_query'][] = [
                'relation' => 'OR',
                [
                    'key'     => growfund_with_prefix(UserSupport::IS_ANONYMIZED),
                    'compare' => 'NOT EXISTS',
                ],
                [
                    'key'     => growfund_with_prefix(UserSupport::IS_ANONYMIZED),
                    'value'   => '1',
                    'compare' => '!=',
                ],
            ];
        }

        $query = new WP_User_Query($query_args);

        if ($this->has_campaign_id($params)) {
            remove_filter('pre_user_query', $campaign_filter_callback);
        }

        if (growfund_user()->is_fundraiser()) {
            remove_filter('pre_user_query', $fundraiser_filter_callback);
        }

        $results = [];

        $users = $query->get_results();

        if (!empty($users)) {
            foreach ($users as $user) {
                $results[] = $this->format_data($user);
            }
        }

        $total = $query->get_total();
        $overall = PaginationSupport::get_overall_user_count(Backer::ROLE);

        return Paginator::make_metadata(
            $results,
            (int) $limit,
            (int) $page,
            $total,
            $overall
        );
    }

    /**
     * Check if the given $params contains a non-empty campaign_id key.
     *
     * @param array $params associative array containing the query parameters.
     *
     * @return bool true if the campaign_id key is set and not empty, false otherwise.
     */
    private function has_campaign_id(array $params)
    {
        return isset($params['campaign_id']) && !empty($params['campaign_id']);
    }

    /**
     * Format backer data into API-friendly schema.
     *
     * @param \WP_User $backer WordPress backer object.
     * @return BackerDTO.
     */
    protected function format_data($backer)
    {
        return BackerDTO::from_array([
            'id'                      => (string) $backer->ID,
            'first_name'              => $backer->first_name ?? '',
            'last_name'               => $backer->last_name ?? '',
            'email'                   => $backer->user_email,
            'username'                => $backer->user_login,
            'phone'                   => UserSupport::get_phone_number($backer->ID),
            'image'                   => UserSupport::get_avatar_image($backer->ID),
            'number_of_contributions' => $this->pledge_service->get_total_number_of_pledges((int) $backer->ID),
            'total_contributions'     => $this->pledge_service->get_total_pledges_amount((int) $backer->ID),
            'latest_pledge_date'      => $this->pledge_service->get_latest_pledge_date($backer->ID),
            'joined_at'               => $backer->user_registered,
            'created_by'              => UserSupport::get_created_by($backer->ID),
            'shipping_address'        => UserSupport::get_shipping_address($backer->ID),
            'billing_address'         => UserSupport::get_billing_address($backer->ID),
            'is_billing_address_same' => UserSupport::is_billing_address_same($backer->ID),
            'is_verified'             => UserSupport::is_verified($backer),
            'is_fundraiser'           => UserSupport::is_fundraiser($backer),
        ]);
    }

    /**
     * Get Backer by id.
     * 
     * @param int $id Backer id.
     * @return BackerDTO.
     */
    public function get_by_id(int $id)
    {
        $user = growfund_user($id);

        if (!$user->is_backer() || !$this->is_user_accessible_for_fundraiser($id)) {
            throw new Exception(esc_html__('Backer not found', 'growfund'), (int) Response::NOT_FOUND);
        }

        return $this->format_data($user->get());
    }

    /**
     * Get Backer overview by id.
     * 
     * @param int $id Backer id.
     * @return \Growfund\DTO\Backer\BackerOverviewDTO.
     */
    public function get_overview(int $id)
    {
        return BackerOverviewDTO::from_array([
            'pledged_amount' => 0,
            'backed_amount' => 0,
            'pledged_campaigns' => 0,
            'backed_campaigns' => 0,
            'backer_information' => $this->get_by_id($id),
            'activity_logs' => [],
        ]);
    }

    /**
     * Delete a backer by id.
     * 
     * @param int $id Backer id.
     * @param string $type Whether to delete permanently or just mark as deleted or anonymize.
     * @return bool True if delete was successful, false otherwise.
     * @throws Exception If backer not found.
     */
    public function delete(int $id, $type = null)
    {
        $backer = get_user_by('ID', $id);

        if (!$backer || !in_array(Backer::ROLE, $backer->roles, true)) {
            throw new Exception(esc_html__('Backer not found', 'growfund'), (int) Response::NOT_FOUND);
        }

        parent::delete($id, $type);

        return true;
    }

    /**
     * Delete all the trashed backer
     * 
     * @param bool $is_permanent_delete
     * @return bool
     */
    public function empty_trash($is_permanent_delete = false)
    {
        $users = get_users([
            'role'       => Backer::ROLE,
            'meta_key'   => growfund_with_prefix(UserSupport::SOFT_DELETE_KEY), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
            'meta_value' => true, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
            'fields'     => 'ID',
            'number'     => -1,
        ]);

        if (empty($users)) {
            return false;
        }

        $succeeded = [];
        $failed = [];

        foreach ($users as $user_id) {
            $delete_type = $is_permanent_delete ? UserDeleteType::PERMANENT : UserDeleteType::ANONYMIZE;
            $deleted = $this->delete($user_id, $delete_type);

            if ($deleted) {
                $succeeded[] = $user_id;
            } else {
                $failed[] = $user_id;
            }
        }

        return count($succeeded) > 0;
    }

    /**
     * Delete multiple existing backer by their id's with associated metadata.
     *
     * @param array $ids The ID's of the backer.
     * @param string $type Whether to delete permanently or just mark as deleted or anonymize.
     * @return array Response array with success and failure messages.
     * @throws Exception If something went wrong.
     */
    public function bulk_delete(array $ids, $type = null)
    {
        $succeeded = [];
        $failed = [];

        foreach ($ids as $id) {
            try {
                $result = $this->delete($id, $type);

                if ($result === false) {
                    $failed[] = [
                        'id' => $id,
                        'message' => ($type === UserDeleteType::PERMANENT || $type === UserDeleteType::ANONYMIZE)
                            ? __('Backer could not be deleted.', 'growfund')
                            : __('Backer could not be trashed.', 'growfund'),
                    ];
                } else {
                    $succeeded[] = [
                        'id' => $id,
                        'message' => ($type === UserDeleteType::PERMANENT || $type === UserDeleteType::ANONYMIZE)
                            ? __('Backer has been deleted.', 'growfund')
                            : __('Backer has been trashed.', 'growfund'),
                    ];
                }
            } catch (Exception $error) {
                $failed[] = [
                    'id' => $id,
                    'message' => $error->getMessage(),
                ];
            }
        }

        return [
            'succeeded' => $succeeded,
            'failed' => $failed,
        ];
    }

    /**
     * Restore multiple trashed backers by their ids.
     * Iterates through each id and attempts to restore the corresponding backer.
     * Collects information on which backers were successfully restored and which failed.
     *
     * @param array<int> $ids The ids of the backers to be restored.
     * @return array Contains 'succeeded' and 'failed' arrays with id and message for each backer.
     * @throws Exception If an error occurs during the restoration process.
     */
    public function bulk_restore(array $ids)
    {
        $succeeded = [];
        $failed = [];

        foreach ($ids as $id) {
            try {
                $result = $this->restore($id);

                if ($result === false) {
                    $failed[] = [
                        'id' => $id,
                        'message' => __('Backer could not be restored.', 'growfund'),
                    ];
                } else {
                    $succeeded[] = [
                        'id' => $id,
                        'message' => __('Backer has been restored.', 'growfund'),
                    ];
                }
            } catch (Exception $error) {
                $failed[] = [
                    'id' => $id,
                    'message' => $error->getMessage(),
                ];
            }
        }

        return [
            'succeeded' => $succeeded,
            'failed' => $failed,
        ];
    }

    /**
     * Get all the campaign ids contributed by a backer.
     *
     * @param int $backer_id Backer id.
     * @return array<int,array<campaign_id,total_number_of_contributions>> Campaign ids with total number of contributions.
     */
    public function get_campaign_ids_by_backer(int $backer_id)
    {
        $pledges = QueryBuilder::query()
            ->table(Tables::PLEDGES)
            ->select(['campaign_id', 'COUNT(*) as total_number_of_contributions'])
            ->where('user_id', $backer_id)
            ->where_in(
                'status',
                [PledgeStatus::PENDING, PledgeStatus::IN_PROGRESS, PledgeStatus::BACKED, PledgeStatus::COMPLETED]
            )
            ->group_by('campaign_id')
            ->get();

        return Arr::make($pledges)->reduce(function ($carry, $current) {
            $carry[$current->campaign_id] = $current->total_number_of_contributions;
            return $carry;
        }, [])->toArray();
    }
}
