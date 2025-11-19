<?php

namespace Growfund\Services;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\Activities;
use Growfund\Constants\Tables;
use Growfund\Constants\UserDeleteType;
use Growfund\Constants\UserTypes\Donor;
use Growfund\DTO\Activity\ActivityFilterDTO;
use Growfund\DTO\Donation\DonationFilterParamsDTO;
use Growfund\DTO\Donor\DonorDTO;
use Growfund\DTO\Donor\DonorOverviewDTO;
use Growfund\DTO\PaginatedCollectionDTO;
use Growfund\Http\Response;
use Growfund\QueryBuilder;
use Growfund\Supports\MediaAttachment;
use Growfund\Supports\Paginator;
use Growfund\Supports\User;
use Growfund\Supports\UserMeta;
use Growfund\Supports\Pagination as PaginationSupport;
use Exception;
use WP_User_Query;

class DonorService extends UserService
{
    /**
     * @var DonationService
     */
    protected $donation_service;

    /**
     * Constructor
     *
     * @param DonationService $donation_service The service to fetch donations.
     */
    public function __construct()
    {
        $this->donation_service = new DonationService();
    }

    /**
     * Get paginated list of donors.
     *
     * @param array $params Associative array containing:
     *   - int    'limit'        Number of results per page.
     *   - int    'page'         Current page number.
     *   - string 'search'       Search keyword (by ID).
     *   - string 'orderby'      Order by field.
     *   - string 'order'        ASC | DESC.
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
        $limit = !empty($params['limit']) ? (int) $params['limit'] : 10;
        $page = !empty($params['page']) ? (int) $params['page'] : 1;
        $orderby = !empty($params['orderby']) ? (int) $params['orderby'] : 'ID';
        $order = !empty($params['order']) ? (int) $params['order'] : 'DESC';
        $search = !empty($params['search']) ? $params['search'] : '';
        $status = !empty($params['status']) ? $params['status'] : 'all';
        $campaign_id = !empty($params['campaign_id']) ? $params['campaign_id'] : null;

        $query_args = [
            'count_total'    => true,
            'number'         => $limit,
            'paged'          => $page,
            'orderby'        => $orderby,
            'order'          => strtoupper($order),
            'role'           => Donor::ROLE,
        ];

        if (!empty($search)) {
            $query_args['search'] = '*' . $search . '*';
            $query_args['search_columns'] = ['ID', 'user_login', 'user_email', 'user_nicename'];
        }

        if ($status === 'all') {
            $query_args['meta_query'][] = [
                'relation' => 'OR',
                [
                    'key'     => growfund_with_prefix(User::SOFT_DELETE_KEY),
                    'compare' => 'NOT EXISTS',
                ],
                [
                    'key'     => growfund_with_prefix(User::SOFT_DELETE_KEY),
                    'value'   => '1',
                    'compare' => '!=',
                ],
            ];

            $query_args['meta_query'][] = [
                'relation' => 'OR',
                [
                    'key'     => growfund_with_prefix(User::IS_ANONYMIZED),
                    'compare' => 'NOT EXISTS',
                ],
                [
                    'key'     => growfund_with_prefix(User::IS_ANONYMIZED),
                    'value'   => '1',
                    'compare' => '!=',
                ],
            ];
        } elseif ($status === 'trashed') {
            $query_args['meta_query'][] = [
                [
                    'key'     => growfund_with_prefix(User::SOFT_DELETE_KEY),
                    'value'   => '1',
                ],
            ];

            $query_args['meta_query'][] = [
                'relation' => 'OR',
                [
                    'key'     => growfund_with_prefix(User::IS_ANONYMIZED),
                    'compare' => 'NOT EXISTS',
                ],
                [
                    'key'     => growfund_with_prefix(User::IS_ANONYMIZED),
                    'value'   => '1',
                    'compare' => '!=',
                ],
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
            $date_query['compare'] = 'BETWEEN';
            $query_args['date_query'] = [$date_query];
        }

        $campaign_filter_callback = $this->apply_campaign_filter((int) $campaign_id, QueryBuilder::prefix(Tables::DONATIONS));

        if ($campaign_id) {
            add_filter('pre_user_query', $campaign_filter_callback);
        }

        $fundraiser_filter_callback = $this->apply_fundraiser_filter(QueryBuilder::prefix(Tables::DONATIONS));

        if (growfund_user()->is_fundraiser()) {
            add_filter('pre_user_query', $fundraiser_filter_callback);
        }

        $query = new WP_User_Query($query_args);

        if ($campaign_id) {
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
        $overall = PaginationSupport::get_overall_user_count(Donor::ROLE);

        return Paginator::make_metadata(
            $results,
            (int) $limit,
            (int) $page,
            $total,
            $overall
        );
    }

    /**
     * Format user data into API-friendly schema.
     *
     * @param WP_User $user WordPress user object.
     * @return DonorDTO Associative array of formatted user data.
     */
    protected function format_data($user)
    {
        $meta = UserMeta::get_all($user->ID);

        $latest_donation = $this->donation_service->get_latest_contribution($user->ID);
        $latest_donation_date = $latest_donation->created_at ?? null;

        return DonorDTO::from_array([
            'id'                            => (string) $user->ID,
            'first_name'                    => $meta['first_name'] ?? '',
            'last_name'                     => $meta['last_name'] ?? '',
            'email'                         => $user->user_email,
            'phone'                         => $meta['phone'] ?? null,
            'billing_address'               => !empty($meta['billing_address']) ? maybe_unserialize($meta['billing_address']) : null,
            'image'                         => !empty($meta['image']) ? MediaAttachment::make((int) $meta['image']) : null,
            'number_of_contributions'       => $this->donation_service->get_total_number_of_donations($user->ID),
            'total_contributions'           => $this->donation_service->get_total_contribution_amount_by_donor($user->ID),
            'latest_donation_date'          => $latest_donation_date,
            'joined_at'                    => $user->user_registered,
            'is_verified'                   => User::is_verified($user->ID),
            'created_by'                    => $meta['created_by'] ?? null,
        ]);
    }

    /**
     * Get Donor by id.
     * 
     * @param int $id Donor id.
     * @return DonorDTO.
     */
    public function get_by_id(int $id)
    {
        $user = growfund_user($id);

        if (!$user->is_donor() || (growfund_user()->is_fundraiser() && ! $this->is_user_accessible_for_fundraiser($id))) {
            throw new Exception(esc_html__('Donor not found', 'growfund'), (int) Response::NOT_FOUND);
        }

        return $this->format_data($user->get());
    }

    /**
     * Get donor overview
     * @param int $id
     * 
     * @return DonorOverviewDTO
     * 
     * @throws Exception
     */
    public function get_overview(int $id)
    {
        $donor_info = $this->get_by_id($id);

        return DonorOverviewDTO::from_array([
            'id' => (string) $id,
            'total_contributions' => 0,
            'average_donation' => 0,
            'donated_campaigns' => 0,
            'number_of_contributions' => 0,
            'profile' => $donor_info,
            'activity_logs' => [],
        ]);
    }

    /**
     * Get paginated list of a donor's donations.
     * 
     * @param DonationFilterParamsDTO $params_dto The parameters to filter the donations.
     * 
     * @return PaginatedCollectionDTO
     * 
     * @throws Exception If the donor is not found.
     */
    public function get_paginated_donations(DonationFilterParamsDTO $params_dto)
    {
        $donor = get_userdata($params_dto->user_id);

        if (!$donor || !in_array(Donor::ROLE, $donor->roles, true)) {
            throw new Exception(esc_html__("Donor not found", 'growfund'), (int) Response::NOT_FOUND);
        }

        return $this->donation_service->paginated($params_dto);
    }

    /**
     * Delete a donor by id.
     * 
     * @param int $id Donor id.
     * @param string $type Whether to delete permanently or just mark as deleted or anonymize.
     * @return bool True if delete was successful, false otherwise.
     * @throws Exception If donor not found.
     */
    public function delete(int $id, $type = null)
    {
        $donor = get_user_by('ID', $id);

        if (!$donor || !in_array(Donor::ROLE, $donor->roles, true)) {
            throw new Exception(esc_html__('Donor not found', 'growfund'), (int) Response::NOT_FOUND);
        }

        parent::delete($id, $type);

        return true;
    }

    /**
     * Delete all the trashed donors
     * 
     * @param bool $is_permanent_delete
     * @return bool
     */
    public function empty_trash($is_permanent_delete = false)
    {
        $users = get_users([
            'role'       => Donor::ROLE,
            'meta_key'   => growfund_with_prefix(User::SOFT_DELETE_KEY), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
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
     * Delete multiple existing donors by their id's with associated metadata.
     *
     * @param array $ids The ID's of the donors.
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
                        'message' => $type === UserDeleteType::PERMANENT || $type === UserDeleteType::ANONYMIZE
                            ? __('Donor could not be deleted.', 'growfund')
                            : __('Donor could not be trashed.', 'growfund'),
                    ];
                } else {
                    $succeeded[] = [
                        'id' => $id,
                        'message' => $type === UserDeleteType::PERMANENT || $type === UserDeleteType::ANONYMIZE
                            ? __('Donor has been deleted.', 'growfund')
                            : __('Donor has been trashed.', 'growfund'),
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
     * Restore multiple trashed donors by their ids.
     * Iterates through each id and attempts to restore the corresponding donor.
     * Collects information on which donors were successfully restored and which failed.
     *
     * @param array $ids The ids of the donors to be restored.
     * @return array Contains 'succeeded' and 'failed' arrays with id and message for each donor.
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
                        'message' => __('Donor could not be restored.', 'growfund'),
                    ];
                } else {
                    $succeeded[] = [
                        'id' => $id,
                        'message' => __('Donor has been restored.', 'growfund'),
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
}
