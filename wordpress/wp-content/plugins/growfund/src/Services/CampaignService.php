<?php

namespace Growfund\Services;

defined( 'ABSPATH' ) || exit;

use Growfund\App\Events\CampaignEndedEvent;
use Growfund\App\Events\CampaignStatusUpdateEvent;
use Growfund\App\Events\CampaignUpdateEvent;
use Growfund\Constants\AppreciationType;
use Growfund\Constants\Campaign\ReachingAction;
use Growfund\Constants\Tables;
use Growfund\Constants\DateTimeFormats;
use Growfund\Constants\Pagination;
use Growfund\Supports\Pagination as PaginationSupport;
use Growfund\Constants\PostStatus;
use Growfund\Constants\Status\CampaignSecondaryStatus;
use Growfund\Constants\Status\CampaignStatus;
use Growfund\Constants\WP;
use Growfund\DTO\Campaign\CampaignDTO;
use Growfund\DTO\Campaign\CampaignFiltersDTO;
use Growfund\DTO\Campaign\UpdateCampaignDTO;
use Growfund\DTO\Donation\DonationFilterParamsDTO;
use Growfund\DTO\Pledge\PledgeFilterParamsDTO;
use Growfund\DTO\PaginatedCollectionDTO;
use Growfund\PostTypes\Campaign;
use Growfund\PostTypes\Reward;
use Growfund\QueryBuilder;
use Growfund\Sanitizer;
use Growfund\Services\Analytics\CampaignAnalyticService;
use Growfund\Services\Analytics\DonationAnalyticService;
use Growfund\Supports\Arr;
use Growfund\Supports\Date;
use Growfund\Supports\MediaAttachment;
use Growfund\Supports\PostMeta;
use Growfund\Supports\Terms;
use Growfund\Taxonomies\Category;
use Growfund\Taxonomies\Tag;
use DateTime;
use Exception;
use Growfund\Constants\Campaign\FundSelectionType;
use Growfund\Constants\Campaign\SuggestedOptionType;
use Growfund\Constants\Campaign\TributeNotificationPreference;
use Growfund\Constants\HookNames;
use Growfund\DTO\Fundraiser\CollaboratorDTO;
use Growfund\Supports\MetaQueryFilter;
use WP_Post;
use WP_Query;

class CampaignService
{
    /** @var PledgeService */
    protected $pledge_service;

    /** @var DonationService */
    protected $donation_service;

    /**
     * Initialize the service with dependencies.
     */
    public function __construct()
    {
        $this->pledge_service = new PledgeService();
        $this->donation_service = new DonationService();
    }

    /**
     * Get campaigns with pagination.
     *
     * @param CampaignFiltersDTO $filters_dto The filters to apply to the query.
     * @return PaginatedCollectionDTO The dto containing campaign records and related metadata.
     */
    public function paginated(CampaignFiltersDTO $filters_dto)
    {
        $limit = $filters_dto->limit;
        $page = $filters_dto->page;

        $query = $this->get_query($filters_dto);

        $results = [];

        if ($query->have_posts()) {
            foreach ($query->posts as $campaign) {
                $results[] = $this->prepare_campaign_dto($campaign);
            }
        }

        remove_filter('posts_where', [$this, 'filter_out_non_admin_draft_campaigns']);
        remove_filter('posts_where', $this->filter_out_campaigns_by_author($filters_dto));
        remove_filter('posts_where', [new MetaQueryFilter(), 'update_meta_value_is_null']);

        return PaginatedCollectionDTO::from_array([
            'results' => $results,
            'total' => $query->found_posts,
            'count' => count($results),
            'per_page' => $limit,
            'current_page' => $page,
            'has_more' => $page < $query->max_num_pages,
            'overall' => PaginationSupport::get_overall_post_count(Campaign::NAME, ['post__in' => $query_args['post__in'] ?? []]),
        ]);
    }

    /**
     * Get query
     * @param CampaignFiltersDTO $filters_dto
     * @return WP_Query
     */
    protected function get_query(CampaignFiltersDTO $filters_dto) {
		$limit = $filters_dto->limit;
        $page = $filters_dto->page;

        $query_args = [
            'post_type'      => Campaign::NAME,
            'posts_per_page' => $limit,
            'post_status'    => ['publish', 'draft', 'trash'],
            'paged'          => $page,
        ];

        $query_args = $this->apply_sorting($filters_dto, $query_args);

        // Make a way so that we can get the campaigns within the provided ids only
        if (!empty($filters_dto->post_ids)) {
            $query_args['post__in'] = $filters_dto->post_ids;
        }

        if (!empty($filters_dto->post__not_in_ids)) {
            $query_args['post__not_in'] = $filters_dto->post__not_in_ids;
        }

        // Search by title/content
        if (!empty($filters_dto->search)) {
            $query_args['s'] = $filters_dto->search;
        }

        if (!empty($filters_dto->category_slug)) {
            $query_args['tax_query'][] = [
                'taxonomy' => Category::NAME,
                'field'    => 'slug',
                'terms'    => $filters_dto->category_slug,
                'include_children' => true
            ];
        }

        if (!empty($filters_dto->tag)) {
            $query_args['tax_query'][] = [
                'taxonomy' => Tag::NAME,
                'field'    => 'name',
                'terms'    => is_array($filters_dto->tag) ? $filters_dto->tag : explode(',', $filters_dto->tag),
                'operator' => 'IN',
            ];
        }

        // Filter by custom meta field 'status'
        if (empty($filters_dto->status)) {
            $filters_dto->status = 'all';
        }

        switch ($filters_dto->status) {
            case 'all':
                $query_args['meta_query'][] = [
                    'key'       => growfund_with_prefix('status'),
                    'value'     => CampaignStatus::TRASHED,
                    'compare'   => '!=',
                ];
                break;
            case 'launched':
                $query_args['meta_query'][] = [
                    'relation' => 'AND',
                    [
                        'key'     => growfund_with_prefix('status'),
                        'value'   => CampaignStatus::PUBLISHED,
                        'compare' => '='
                    ],
                    [
                        'key'     => growfund_with_prefix('start_date'),
                        'value'   => current_time(DateTimeFormats::DB_DATETIME),
                        'compare' => '<=',
                        'type'    => 'DATE',
                    ],
                    [
						'relation' => 'OR',
						[
							'key'     => growfund_with_prefix('end_date'),
							'value'   => Date::current_sql_safe(),
							'compare' => '>=',
							'type'    => 'DATE',
						],
						[
                            'key'     => growfund_with_prefix('end_date'),
							'value' => 'IS NULL',
                        ]
					],
                    [
                        'relation' => 'OR',
						[
							'key'     => growfund_with_prefix('is_ended'),
							'compare' => 'NOT EXISTS',
						],
						[
							'key'     => growfund_with_prefix('is_ended'),
							'value'   => 1,
							'compare' => '!=',
						],
                    ],
                ];

                break;
            case 'launched-and-beyond':
                $query_args['meta_query'][] = [
                    'relation' => 'AND',
                    [
                        'key'     => growfund_with_prefix('status'),
                        'value'   => [CampaignStatus::PUBLISHED, CampaignStatus::FUNDED],
                        'compare' => 'IN'
                    ],
                    [
                        'key'     => growfund_with_prefix('start_date'),
                        'value'   => Date::current_sql_safe(),
                        'compare' => '<=',
                        'type'    => 'DATE',
                    ],
					[
						'relation' => 'OR',
						[
							'key'     => growfund_with_prefix('end_date'),
							'value'   => Date::current_sql_safe(),
							'compare' => '>=',
							'type'    => 'DATE',
						],
						[
                            'key'     => growfund_with_prefix('end_date'),
							'value' => 'IS NULL',
                        ]
					],
                    [
                        'relation' => 'OR',
						[
							'key'     => growfund_with_prefix('is_ended'),
							'compare' => 'NOT EXISTS',
						],
						[
							'key'     => growfund_with_prefix('is_ended'),
							'value'   => 1,
							'compare' => '!=',
						],
                    ],
                ];

                break;
            default:
                $query_args['meta_query'][] = [
                    'key'     => growfund_with_prefix('status'),
                    'value'   => strpos($filters_dto->status, ',')
                        ? explode(',', $filters_dto->status)
                        : $filters_dto->status,
                    'compare' => strpos($filters_dto->status, ',') ? 'IN' : '='
                ];
                break;
        }

        if (!empty($filters_dto->is_featured)) {
            $query_args['meta_query'][] = [
                'key'     => growfund_with_prefix('is_featured'),
                'value'   => $filters_dto->is_featured ? '1' : '0',
                'compare' => '='
            ];
        }

        // Filter by start date
        if (!empty($filters_dto->start_date)) {
            $query_args['meta_query'][] = [
                'key'     => growfund_with_prefix('start_date'),
                'value'   => $filters_dto->start_date,
                'compare' => '>=',
                'type'    => 'DATE'
            ];
        }

        // Filter by end date
        if (!empty($filters_dto->end_date)) {
            $query_args['meta_query'][] = [
                'key'     => growfund_with_prefix('end_date'),
                'value'   => $filters_dto->end_date,
                'compare' => '<=',
                'type'    => 'DATE'
            ];
        }

        // Ensure meta_query uses AND logic only if more than one condition exists
        if (count($query_args['meta_query']) > 1) {
            $query_args['meta_query']['relation'] = 'AND';
        }

        add_filter('posts_where', $this->filter_out_campaigns_by_author($filters_dto));
        add_filter('posts_where', [$this, 'filter_out_non_admin_draft_campaigns']);
        add_filter('posts_where', [new MetaQueryFilter(), 'update_meta_value_is_null']);

        $query = new WP_Query($query_args);

        return $query;
    }

    protected function apply_sorting(CampaignFiltersDTO $filters_dto, array $query_args)
    {
        $query_args['order'] = strtoupper($filters_dto->order ?? 'DESC');

        if (empty($filters_dto->orderby)) {
            $query_args['orderby'] = 'ID';

            return $query_args;
        }

        switch ($filters_dto->orderby) {
            case 'created_date':
                $query_args = array_merge($query_args, [
                    'orderby' => 'date',
                ]);
                break;
            case 'start_date': 
                $query_args = array_merge($query_args, [
                    'orderby' => 'meta_value',
                    'meta_key' => growfund_with_prefix('start_date'), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
                    'meta_type' => 'DATE' // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
                ]);
                break;
			case 'end_date':
                $query_args = array_merge($query_args, [
                    'orderby' => 'meta_value',
                    'meta_key' => growfund_with_prefix('end_date'), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
                    'meta_type' => 'DATE' // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
                ]);
                break;
            default:
                $query_args['orderby'] = $filters_dto->orderby;
                break;
        }
        
        return $query_args;
    }

    /**
     * Filters out campaigns by the given author id.
     * If the author id is empty, the filter does nothing.
     * If the post_ids are provided, the filter will merge them with the campaign ids returned by get_campaign_ids_by_collaborator
     * and filter out the campaigns that are not in the resulting array.
     * If the post_ids are not provided, the filter will only filter out the campaigns that are not created by the given author id.
     * @param CampaignFiltersDTO $filters_dto
     * @return callable
     */
    public function filter_out_campaigns_by_author(CampaignFiltersDTO $filters_dto)
    {
        return function ($where) use ($filters_dto) {
            if (empty($filters_dto->author_id)) {
                return $where;
            }

            $post_table = QueryBuilder::prefix(WP::POSTS_TABLE);
            $campaign_ids = $this->get_campaign_ids_by_collaborator($filters_dto->author_id);

            if (!empty($filters_dto->post_ids)) {
                $campaign_ids = array_unique(array_merge($campaign_ids, $filters_dto->post_ids));
            }

            if (!empty($campaign_ids)) {
                $placeholders = Arr::make($campaign_ids)->map(function () {
                    return '%d';
                })->join(',');
                $where .= QueryBuilder::get_db()->prepare(
                    " AND ({$post_table}.ID IN ($placeholders) OR {$post_table}.post_author = %d) ",
                    array_merge($campaign_ids, [$filters_dto->author_id])
                );

                return $where;
            }

            $where .= " AND {$post_table}.post_author = $filters_dto->author_id";

            return $where;
        };
    }

    /**
     * Filter out the non admin author's draft campaigns from the admin's campaign list
     *
     * @param string $where
     * @return string
     */
    public function filter_out_non_admin_draft_campaigns($where)
    {
        if (!growfund_user()->is_admin()) {
            return $where;
        }

        $post_table = QueryBuilder::query()->table(WP::POSTS_TABLE)->get_table_name();
        $post_meta_table = QueryBuilder::query()->table(WP::POST_META_TABLE)->get_table_name();

        return $where . sprintf(
            " AND %s.ID NOT IN 
                (
                    SELECT p.ID from %s as p INNER JOIN %s as pm ON p.ID = pm.post_id
                    WHERE p.post_author != %d
                    AND pm.meta_key = '%s'
                    AND pm.meta_value = '%s'
                )
            ",
            $post_table,
            $post_table,
            $post_meta_table,
            growfund_user()->get_id(),
            growfund_with_prefix('status'),
            CampaignStatus::DRAFT
        );
    }

    /**
     * Get recommended campaigns.
     *
     * @param string $category_slug The category slug.
     * @return CampaignDTO[] The recommended campaigns.
     */
    public function get_recommended_campaigns(string $category_slug)
    {
        $filters_dto = new CampaignFiltersDTO();
        $filters_dto->limit = 4;
        $filters_dto->category_slug = $category_slug;
        $filters_dto->order = 'desc';
        $filters_dto->orderby = 'rand';

        $query = $this->get_query($filters_dto);

        if ($query->have_posts()) {
            foreach ($query->posts as $campaign) {
                $results[] = $this->prepare_campaign_dto($campaign);
            }
        }

        remove_filter('posts_where', [$this, 'filter_out_non_admin_draft_campaigns']);
        remove_filter('posts_where', $this->filter_out_campaigns_by_author($filters_dto));
        remove_filter('posts_where', [new MetaQueryFilter(), 'update_meta_value_is_null']);

        return $results;
    }

    /**
     * Get campaigns by user id.
     *
     * @param CampaignFiltersDTO $filters_dto The filters to apply to the query.
     * @return int The number of campaigns.
     */
    public function get_count_by_user_id(int $id)
    {
        $query_args = [
            'post_type'      => Campaign::NAME,
            'author'         => $id,
            'numberposts' => -1,
            'post_status' => 'any',
        ];

        $campaigns = get_posts($query_args);

        return count($campaigns ?? []);
    }

    /**
     * Get campaign by id.
     *
     * @param int $id The id of the campaign.
     * @return CampaignDTO The campaign record.
     * @throws Exception If the post could not be found.
     */
    public function get_by_id(int $id)
    {
        $result = [];

        $campaign = get_post($id);

        if (!$campaign || $campaign->post_type !== Campaign::NAME) {
            /* translators: %s: campaign id */
            throw new Exception(sprintf(esc_html__('Campaign with ID %s not found.', 'growfund'), esc_html($id)));
        }

        $result = $this->prepare_campaign_dto($campaign);

        return $result;
    }


    /**
     * Create a new campaign post with associated metadata, taxonomies, and collaborators.
     *
     * @return int The ID of the newly created campaign post.
     * @throws Exception If the post could not be inserted.
     */
    public function create(): int
    {
        $post_id = wp_insert_post([
            'post_type'    => Campaign::NAME,
            'post_title'   => 'Untitled',
            'post_status'  => Campaign::DEFAULT_POST_STATUS,
            'post_author'  => get_current_user_id(),
            'comment_status' => 'open'
        ], true);

        if (is_wp_error($post_id)) {
            /* translators: %s: error message */
            throw new Exception(sprintf(esc_html__('Failed to create campaign: %s', 'growfund'), esc_html($post_id->get_error_message())));
        }

        PostMeta::add($post_id, 'status', CampaignStatus::DRAFT);
        PostMeta::add($post_id, 'start_date', Date::current_sql_safe());

        return $post_id;
    }

    /**
     * Update an existing campaign post with associated metadata.
     *
     * @param int $id
     * @param UpdateCampaignDTO $data
     * @return bool 
     * @throws Exception
     */
    public function update(int $id, UpdateCampaignDTO $dto): bool
    {
        $campaign = $this->get_by_id($id);

        // Update the post
        $campaign_id = wp_update_post([
            'ID'           => $id,
            'post_title'   => $dto->title,
            'post_name'    => $dto->slug && substr($dto->slug, 0, 8) !== 'untitled'
                ? $dto->slug
                : Sanitizer::apply_rule($dto->title, Sanitizer::TITLE),
            'post_content' => $dto->description ?? '',
        ], true);

        if (is_wp_error($campaign_id)) {
            throw new Exception(
                sprintf(
                    /* translators: %s: campaign id */
                    esc_html__('Failed to update campaign with ID %s.', 'growfund'),
                    esc_html($id)
                )
            );
        }

        $dto = $this->clear_unused_data_based_on_appreciation_type($dto);

        PostMeta::update_many($campaign_id, $dto->get_meta(['collaborators', 'start_date']));

        $this->continue_campaign_when_reaching_action_is_changed($campaign, $dto);

        if (empty(PostMeta::get($id, 'start_date'))) {
            PostMeta::update($campaign_id, 'start_date', Date::current_sql_safe());
        }

        // Set category & sub categories
        $categories = [
            $dto->category ?? 0,
            $dto->sub_category ?? 0
        ];

        wp_set_object_terms($campaign_id, $categories, Category::NAME);
        wp_set_object_terms($campaign_id, $dto->tags, Tag::NAME, false);

        do_action(HookNames::GROWFUND_CAMPAIGN_AFTER_SAVE_ACTION, $campaign_id, $dto); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound

        growfund_event(new CampaignUpdateEvent($campaign, $dto));
        growfund_event(new CampaignStatusUpdateEvent($campaign, $dto->status));

        return !empty($campaign_id);
    }

    protected function continue_campaign_when_reaching_action_is_changed(CampaignDTO $campaign, UpdateCampaignDTO $dto){
        if ($campaign->reaching_action === $dto->reaching_action) {
            return;
        }

		if ($campaign->status === CampaignStatus::FUNDED) {
            if ($dto->reaching_action === ReachingAction::CONTINUE && $campaign->is_ended) {
                PostMeta::delete($campaign->id, 'is_ended');
            }

            if ($dto->reaching_action === ReachingAction::CLOSE && !$campaign->is_ended) {
                $this->mark_campaign_as_ended($campaign->id);
            }
        }
    }

    /**
     * Clear the unused data based on the appreciation type.
     * If appreciation type is switch to goodies from giving-thanks, then on saving the data
     * the unused thanks_giving data is not required anymore.
     * This will apply for the giving-thanks to goodies similarly.
     *
     * @param UpdateCampaignDTO $dto
     * @return UpdateCampaignDTO
     */
    protected function clear_unused_data_based_on_appreciation_type(UpdateCampaignDTO $dto)
    {
        if ($dto->appreciation_type === AppreciationType::GIVING_THANKS) {
            $rewards = get_posts([
                'post_type' => Reward::NAME,
                'post_parent' => $dto->id,
                'posts_per_page' => -1,
            ]);

            foreach ($rewards as $reward) {
                wp_delete_post($reward->ID, true);
            }
            $dto->rewards = [];
        } elseif ($dto->appreciation_type === AppreciationType::GOODIES) {
            $dto->giving_thanks = [];
        }

        return $dto;
    }

    /**
     * Delete an existing campaign post with associated metadata.
     *
     * @param int $id The ID of the campaign post.
     * @param bool $force Force delete or trash
     * @return bool True if successfully updated the campaign or false.
     * @throws Exception If the post could not be inserted.
     */
    public function delete(int $id, bool $force = false): bool
    {
        $campaign = get_post($id);

        if (!$campaign || $campaign->post_type !== Campaign::NAME) {
            /* translators: %s: campaign id */
            throw new Exception(sprintf(esc_html__('Campaign with ID %s not found.', 'growfund'), esc_html($id)));
        }

        if ($force) {
            $deleted_campaign = wp_delete_post($id, true);
            $deleted_rewards = (new RewardService())->delete_by_parent_id($id);
            $is_deleted_reward_items = (new RewardItemService())->delete_by_parent_id($id);
            $deleted_campaign_posts = (new CampaignPostService())->delete_by_parent_id($id);

            if (!empty($deleted_campaign)) {
                do_action(HookNames::GROWFUND_CAMPAIGN_AFTER_PERMANENT_DELETE_ACTION, $id); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
            }

            return !empty($deleted_campaign);
        }

        $trashed_campaign = wp_trash_post($id);
        PostMeta::update($id, 'status', CampaignStatus::TRASHED);

        return !empty($trashed_campaign);
    }

    /**
     * Delete existing campaign post by user id
     *
     * @param int $id The ID of the campaign post.
     * @return bool True if successfully deleted the campaign.
     * @throws Exception If the post could not be deleted.
     */
    public function delete_by_user_id(int $id): bool
    {
        $campaigns = get_posts([
            'post_type' => Campaign::NAME,
            'author' => $id,
            'numberposts' => -1,
            'post_status' => 'any',
        ]);

        foreach ($campaigns as $campaign) {
            $this->delete($campaign->ID, true);
        }

        return true;
    }

    /**
     * Restore a trashed campaign post with associated metadata.
     *
     * @param int $id The ID of the campaign post.
     * @return bool True if successfully restored the campaign or false.
     * @throws Exception If the post could not be restored.
     */
    public function restore(int $id): bool
    {
        if (get_post_status($id) === PostStatus::TRASH) {
            $restored = wp_untrash_post($id);
            wp_update_post([
                'ID' => $id,
                'post_status' => 'publish',
            ]);
            PostMeta::update($id, 'status', CampaignStatus::DRAFT);

            return !empty($restored);
        }

        return false;
    }

    /**
     * Delete multiple existing campaigns by their id's with associated metadata.
     *
     * @param array $ids The ID's of the campaigns.
     * @param bool $force Force delete or trash
     * @return array Response array with success and failure messages.
     * @throws Exception If something went wrong.
     */
    public function bulk_delete(array $ids, bool $force = false)
    {
        $succeeded = [];
        $failed = [];

        foreach ($ids as $id) {
            try {
                $result = $this->delete($id, $force);

                if ($result === false) {
                    $failed[] = [
                        'id' => $id,
                        'message' => $force
                            ? __('Campaign could not be deleted.', 'growfund')
                            : __('Campaign could not be trashed.', 'growfund'),
                    ];
                } else {
                    $succeeded[] = [
                        'id' => $id,
                        'message' => $force
                            ? __('Campaign has been deleted.', 'growfund')
                            : __('Campaign has been trashed.', 'growfund'),
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
     * Delete all the trashed campaigns
     *
     * @param int|null $user_id
     * @return bool
     */
    public function empty_trash($user_id = null)
    {
        $query_args = [
            'post_type' => Campaign::NAME,
            'post_status' => [PostStatus::TRASH],
            'fields' => 'ids',
            'posts_per_page' => -1,
        ];

        if ($user_id) {
            $query_args['author'] = $user_id;
        }

        $query = new WP_Query($query_args);

        if (is_wp_error($query)) {
            return false;
        }

        $ids = $query->posts;

        try {
            $response = $this->bulk_delete($ids, true);
        } catch (Exception $error) {
            return false;
        }

        return count($response['succeeded']) > 0;
    }

    /**
     * Restore multiple trashed campaigns by their ids.
     * Iterates through each id and attempts to restore the corresponding campaign.
     * Collects information on which campaigns were successfully restored and which failed.
     *
     * @param array<int> $ids The ids of the campaigns to be restored.
     * @return array Contains 'succeeded' and 'failed' arrays with id and message for each campaign.
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
                        'message' => __('Campaign could not be restored.', 'growfund'),
                    ];
                } else {
                    $succeeded[] = [
                        'id' => $id,
                        'message' => __('Campaign has been restored.', 'growfund'),
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
     * Update the featured status of a campaign.
     *
     * @param int $id The ID of the campaign.
     * @param bool $featured The featured status of the campaign.
     * @return bool True if successfully updated the campaign featured status or false.
     *
     * @throws Exception If the campaign could not be found.
     */
    protected function update_featured_status(int $id, bool $featured)
    {
        $campaign = get_post($id);

        if (!$campaign) {
            /* translators: %s: campaign id */
            throw new Exception(sprintf(esc_html__('Campaign with ID %s not found.', 'growfund'), esc_html($id)));
        }

        return PostMeta::update($id, 'is_featured', $featured);
    }

    /**
     * Update the featured status of multiple campaigns.
     *
     * @param array $ids The IDs of the campaigns.
     * @param bool $featured The featured status of the campaigns.
     * @return array Contains 'succeeded' and 'failed' arrays with id and message for each campaign.
     */
    protected function update_bulk_featured_status(array $ids, bool $featured)
    {
        $succeeded = [];
        $failed = [];

        foreach ($ids as $id) {
            try {
                $result = $this->update_featured_status($id, $featured);

                if (!$result) {
                    $failed[] = [
                        'id' => $id,
                        'message' => __('Campaign could not be updated.', 'growfund'),
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
     * Update the featured status of multiple campaigns to featured.
     *
     * @param array $ids The IDs of the campaigns.
     * @return array Contains 'succeeded' and 'failed' arrays with id and message for each campaign.
     */
    public function bulk_featured(array $ids)
    {
        return $this->update_bulk_featured_status($ids, true);
    }

    /**
     * Update the featured status of multiple campaigns to non-featured.
     *
     * @param array $ids The IDs of the campaigns.
     * @return array Contains 'succeeded' and 'failed' arrays with id and message for each campaign.
     */
    public function bulk_non_featured(array $ids)
    {
        return $this->update_bulk_featured_status($ids, false);
    }

    /**
     * Update an existing campaign's status.
     *
     * @param int $id The ID of the campaign post.
     * @param string $status The status of the campaign post.
     * @param string $decline_reason The reason for declining the campaign.
     * @return bool True if successfully updated the campaign status or false.
     * @throws Exception If the post could not be found.
     */
    public function update_status(int $id, string $status, $decline_reason = null): bool
    {
        $campaign = get_post($id);

        if (!$campaign || $campaign->post_type !== Campaign::NAME) {
            /* translators: %s: campaign id */
            throw new Exception(sprintf(esc_html__('Campaign with ID %s not found.', 'growfund'), esc_html($id)));
        }

        if ($status === CampaignStatus::TRASHED) {
            return $this->delete($id, false);
        }

        $campaign_dto = $this->prepare_campaign_dto($campaign);
        $is_declined = false;

        if (!is_null($decline_reason) && $status === CampaignStatus::DECLINED) {
            $is_declined = PostMeta::add($id, 'decline_reasons', [
                'user_id' => get_current_user_id(),
                'message' => $decline_reason,
                'created_at' => Date::current_sql_safe(),
            ]);
        }

        if ($status === CampaignStatus::FUNDED && PostMeta::get($id, 'reaching_action') === ReachingAction::CLOSE) {
            $this->mark_campaign_as_ended($id);
        }

        if ($status === CampaignStatus::COMPLETED) {
            $this->mark_campaign_as_ended($id);
        }

        $is_updated = PostMeta::update($id, 'status', $status) || $is_declined ? true : false;

        if (!empty($is_updated)) {
            growfund_event(new CampaignStatusUpdateEvent($campaign_dto, $status));
        }

        return $is_updated;
    }


    /**
     * Mark a campaign as half goal achieved.
     * 
     * @param int $id The ID of the campaign.
     * @return bool True if the campaign was marked as half goal achieved, false otherwise.
     */
    public function marked_as_half_goal_achieved(int $id)
    {
        $is_updated = PostMeta::update($id, 'is_half_goal_achieved_already', true);

        return !empty($is_updated);
    }

    /**
     * Retrieve the campaign overview details.
     * 
     * @param int $id The ID of the campaign post.
     * @param string $start_date The start date for the date range.
     * @param string $end_date The end date for the date range.
     * @return array
     * @throws Exception If the campaign with the given ID does not exist.
     */
    public function get_overview_details(int $id, string $start_date, string $end_date): array
    {
        $data = [];
        $start_date = new DateTime($start_date);
        $end_date = new DateTime($end_date);

        if (growfund_app()->is_donation_mode()) {
            $donation_analytic_service = new DonationAnalyticService($this->donation_service);

            $data['metrics'] = $donation_analytic_service->get_metrics($start_date, $end_date, $id);
            $data['revenue_chart_data'] = $donation_analytic_service->get_revenue_chart_data($start_date, $end_date, $id);
            $data['latest_pledges'] = $this->donation_service->latest(DonationFilterParamsDTO::from_array(['limit' => Pagination::LIMIT]));

            return $data;
        }

        $campaign_analytic_service = new CampaignAnalyticService();

        $data['metrics'] = $campaign_analytic_service->get_metrics($start_date, $end_date, $id);
        $data['revenue_chart_data'] = $campaign_analytic_service->get_revenue_chart_data($start_date, $end_date, $id);
        $data['latest_pledges'] = $this->pledge_service->latest(PledgeFilterParamsDTO::from_array(['limit' => Pagination::LIMIT]));

        return $data;
    }

    /**
     * Check if the campaign is being interactive.
     * We are calling a campaign interactive if it received at least
     * one pledge or donation.
     *
     * @param int|string $campaign_id
     * @return boolean
     */
    public function is_campaign_interactive($campaign_id)
    {
        if (growfund_app()->is_donation_mode()) {
            return $this->donation_service->get_total_number_of_donations_for_campaign($campaign_id) > 0;
        }

        return $this->pledge_service->get_total_number_of_pledges_for_campaign($campaign_id) > 0;
    }

    /**
     * Prepare the campaign data into the expected format.
     * 
     * @param WP_Post $campaign
     * @return CampaignDTO
     */
    protected function prepare_campaign_dto($campaign)
    {
        $terms = Terms::get_terms($campaign->ID, Category::NAME);
        $category = 0;
        $subcategory = 0;

        foreach ($terms as $term) {
            if ($term->parent === 0) {
                $category = $term->term_id;
            } else {
                $subcategory = $term->term_id;
            }
        }

        $tags = Terms::get_term_ids($campaign->ID, Tag::NAME);
        $metadata = PostMeta::get_all($campaign->ID);
        $collaborators = $this->get_collaborators_by_id($campaign->ID);

        $decline_reasons = PostMeta::get($campaign->ID, 'decline_reasons', false) ?? null;
        $last_decline_reason = null;
        if (!empty($decline_reasons) && is_array($decline_reasons) && count($decline_reasons) > 0) {
            $last_decline_reason = $decline_reasons[count($decline_reasons) - 1]['message'] ?? null;
        }

        $dto = new CampaignDTO();

        $dto->id = (string) $campaign->ID;
        $dto->title = $campaign->post_title;
        $dto->slug = $campaign->post_name;
        $dto->description = $campaign->post_content;
        $dto->author_id = $campaign->post_author;
        $dto->created_by = get_the_author_meta('display_name', $campaign->post_author);

        $dto->story = $metadata['story'] ?? null;
        $dto->images = MediaAttachment::make_many($metadata['images'] ?? []);
        $dto->video =  MediaAttachment::make_video($metadata['video'] ?? []);

        $dto->is_featured = isset($metadata['is_featured'])
            ? (bool) $metadata['is_featured']
            : false;

        $dto->category = !empty($category) ? (string) $category : null;
        $dto->sub_category = !empty($subcategory) ? (string) $subcategory : null;

        $dto->start_date = $metadata['start_date'] ?? null;
        $dto->end_date = $metadata['end_date'] ?? null;
        $dto->location = $metadata['location'] ?? null;
        $dto->tags = array_map('strval', $tags);

        $dto->collaborators = $collaborators ?? null;
        $dto->show_collaborator_list = isset($metadata['show_collaborator_list']) ? filter_var($metadata['show_collaborator_list'], FILTER_VALIDATE_BOOLEAN) : false;

        $dto->status = $metadata['status'] ?? 'draft';
        $dto->is_interactive = $this->is_campaign_interactive($campaign->ID);
        $dto->is_launched = !empty($dto->start_date)
            ? Date::is_date_in_past_or_present($dto->start_date)
            : false;

        $dto->risk = $metadata['risk'] ?? null;
        $dto->has_goal = isset($metadata['has_goal'])
            ? filter_var($metadata['has_goal'], FILTER_VALIDATE_BOOLEAN)
            : false;

        $dto->is_half_goal_achieved_already = isset($metadata['is_half_goal_achieved_already'])
            ? filter_var($metadata['is_half_goal_achieved_already'], FILTER_VALIDATE_BOOLEAN)
            : false;

        $dto->goal_type = $metadata['goal_type'] ?? null;
        $dto->goal_amount = $metadata['goal_amount'] ?? null;
        $dto->reaching_action = $metadata['reaching_action'] ?? null;

        $dto->confirmation_title = $metadata['confirmation_title'] ?? null;
        $dto->confirmation_description = $metadata['confirmation_description'] ?? null;
        $dto->provide_confirmation_pdf_receipt = isset($metadata['provide_confirmation_pdf_receipt'])
            ? filter_var($metadata['provide_confirmation_pdf_receipt'], FILTER_VALIDATE_BOOLEAN)
            : false;
        $dto->faqs = !empty($metadata['faqs'])
            ? maybe_unserialize($metadata['faqs'])
            : [];

        if (growfund_app()->is_donation_mode()) {
            $dto->fund_raised = $this->donation_service->get_total_donated_amount($campaign->ID);
            $dto->number_of_contributors = $this->donation_service->get_total_donors_count_for_campaign($campaign->ID);
            $dto->number_of_contributions = $this->donation_service->get_total_count_of_donations_for_campaign($campaign->ID);
            $dto->has_tribute = isset($metadata['has_tribute'])
                ? filter_var($metadata['has_tribute'], FILTER_VALIDATE_BOOLEAN)
                : false;
            $dto->tribute_title = $metadata['tribute_title'] ?? null;
            $dto->tribute_requirement = $metadata['tribute_requirement'] ?? null;
            $dto->tribute_options = !empty($metadata['tribute_options'])
                ? maybe_unserialize($metadata['tribute_options'])
                : [];
            $dto->tribute_notification_preference = $metadata['tribute_notification_preference'] ?? TributeNotificationPreference::LET_DONOR_DECIDE;
            $dto->fund_selection_type = $metadata['fund_selection_type'] ?? FundSelectionType::FIXED;
            $dto->default_fund = $metadata['default_fund'] ?? null;
            $dto->fund_choices = !empty($metadata['fund_choices']) && is_array($metadata['fund_choices'])
                ? array_map('strval', $metadata['fund_choices'])
                : null;
            $dto->allow_custom_donation = isset($metadata['allow_custom_donation'])
                ? filter_var($metadata['allow_custom_donation'], FILTER_VALIDATE_BOOLEAN)
                : false;
            $dto->min_donation_amount = $metadata['min_donation_amount'] ?? null;
            $dto->max_donation_amount = $metadata['max_donation_amount'] ?? null;
            $dto->suggested_option_type = $metadata['suggested_option_type'] ?? SuggestedOptionType::AMOUNT_ONLY;
            $dto->suggested_options = !empty($metadata['suggested_options'])
                ? maybe_unserialize($metadata['suggested_options'])
                : [];
        } else {
            $rewards = [];
            $reward_posts = get_posts([
                'post_type' => Reward::NAME,
                'post_parent' => $campaign->ID,
                'numberposts' => -1,
                'post_status' => 'any',
            ]) ?? [];

            if (!empty($reward_posts)) {
                $rewards = Arr::make($reward_posts)->pluck('ID')->map(function ($id) {
                    return (string) $id;
                })->toArray();
            }

            $dto->fund_raised = $this->pledge_service->get_total_pledges_amount_for_campaign($campaign->ID);
            $dto->number_of_contributors = $this->pledge_service->get_total_backers_count_for_campaign($campaign->ID);
            $dto->number_of_contributions = $this->pledge_service->get_total_number_of_pledges_for_campaign($campaign->ID, true);
            $dto->appreciation_type = $metadata['appreciation_type'] ?? 'goodies';
            $dto->rewards = $rewards;
            $dto->giving_thanks = $metadata['giving_thanks'] ?? null;

            $dto->allow_pledge_without_reward = isset($metadata['allow_pledge_without_reward'])
                ? filter_var($metadata['allow_pledge_without_reward'], FILTER_VALIDATE_BOOLEAN)
                : false;
            $dto->min_pledge_amount = $metadata['min_pledge_amount'] ?? null;
            $dto->max_pledge_amount = $metadata['max_pledge_amount'] ?? null;
            $dto->uncharged_pledge_count = $this->pledge_service->get_total_number_of_uncharged_pledges_for_campaign($campaign->ID);
            $dto->is_backed = $this->pledge_service->is_campaign_backed($campaign->ID);
        }

        $dto->last_decline_reason = $last_decline_reason;
        $dto->preview_url = growfund_campaign_url($campaign->ID);
        $dto->is_paused = boolval($metadata['is_paused'] ?? false);
        $dto->is_hidden = boolval($metadata['is_hidden'] ?? false);
        // If the campaign end date is defined and it reaches the end date
        // Then we mark the campaign as ended.
        // Else if the goal reaching action is set to close
        // Then we mark the campaign as ended.
        // Also if the campaign is marked as ended forcefully then we mark it ended.
        $dto->is_ended = !empty($dto->end_date)
            ? Date::is_date_in_past($dto->end_date)
            : false;
        $dto->is_ended = $dto->is_ended || ($dto->status === CampaignStatus::FUNDED && $dto->reaching_action === ReachingAction::CLOSE) || boolval($metadata['is_ended'] ?? false);


        // To check if the campaign is bookmarked by a user in site
        if (!growfund_is_plugin_menu()) {
            $bookmark_service = new BookmarkService();
            $dto->is_bookmarked = $bookmark_service->is_bookmarked(growfund_user()->get_id(), (int) $dto->id);
        } else {
            $dto->exclude(['is_bookmarked']);
        }

        /**
         * Individual contribution count is the total number of contributions by a specific
         * user. 
         * 
         * This is not required for the admin campaign list, but will updated from the backer/donor specific campaign list APIs.
         */
        $dto->number_of_individual_contributions = 0;

        return $dto;
    }

    /**
     * Gets the IDs of collaborators associated with a given campaign ID.
     *
     * @param int $id The ID of the campaign to retrieve collaborators for.
     * @return array An array of IDs of collaborators associated with the campaign.
     */
    public function get_collaborators_by_id($id)
    {
        $records = QueryBuilder::query()->table(Tables::CAMPAIGN_COLLABORATORS)->select(['collaborator_id'])->where('campaign_id', $id)->get();
        $ids = wp_list_pluck($records, 'collaborator_id');

        return $ids;
    }

    /**
     * Gets the detail of collaborators associated with a given campaign ID.
     *
     * @param int $id The ID of the campaign to retrieve collaborators for.
     * @return array An array of collaborator dto
     */
    public function get_collaborator_list_by_id($id)
    {
        $collaborator_ids = $this->get_collaborators_by_id($id);

        return apply_filters(HookNames::GROWFUND_COLLABORATOR_LIST_FILTER, [], $collaborator_ids); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
    }

    /**
     * @param int|null $author_id
     * @return CollaboratorDTO
     */
    public function get_campaign_author($author_id) {
        if (empty($author_id)) {
            return null;
        }
        
        $author = get_user($author_id);

        if (!$author) {
            return null;
        }
        return (new UserService())->prepare_collaborator_dto($author);
    }

    public function get_campaign_ids_by_collaborator($user_id)
    {
        $records = QueryBuilder::query()->table(Tables::CAMPAIGN_COLLABORATORS)->select(['campaign_id'])->where('collaborator_id', $user_id)->get();
        $ids = wp_list_pluck($records, 'campaign_id');

        return $ids;
    }

    public function get_campaign_ids_by_fundraiser($fundraiser_id)
    {
        $records = QueryBuilder::query()->table(WP::POSTS_TABLE . ' as campaigns')
            ->select(['campaigns.ID as campaign_id'])
            ->join_raw(
                WP::POST_META_TABLE . ' as campaign_status_meta',
                'INNER',
                sprintf("campaigns.ID = campaign_status_meta.post_id AND campaign_status_meta.meta_key = '%s'", growfund_with_prefix('status'))
            )
            ->left_join(Tables::CAMPAIGN_COLLABORATORS . ' as collaborators', 'campaigns.ID', 'collaborators.campaign_id')
            ->where('campaign_status_meta.meta_value', '!=', CampaignStatus::TRASHED)
            ->where_raw(
                "(campaigns.post_author = :author_id OR collaborators.collaborator_id = :collaborator_id)",
                [
                    'author_id' => $fundraiser_id,
                    'collaborator_id' => $fundraiser_id
                ]
            )
            ->get();

        $ids = Arr::make($records ?? [])->pluck('campaign_id')->toArray();

        return $ids;
    }

    /**
     * Gets the IDs of campaigns associated with a given user ID that are in the trash.
     *
     * @param int $user_id The ID of the user to retrieve trashed campaigns for. If null is passed, campaigns for all users will be retrieved.
     * @return array An array of IDs of campaigns associated with the user and in the trash. If no campaigns are found, an empty array is returned.
     */
    public function get_trashed_campaign_ids_by_user($user_id = null)
    {
        $query_args = [
            'post_type' => Campaign::NAME,
            'post_status' => [PostStatus::TRASH],
            'fields' => 'ids',
            'posts_per_page' => -1,
        ];

        $ids = [];

        if ($user_id) {
            $query_args['author'] = $user_id;
            $ids = $this->get_campaign_ids_by_collaborator($user_id) ?? [];
        }

        $query = new WP_Query($query_args);

        if (is_wp_error($query)) {
            throw new Exception(esc_html($query->get_error_message()));
        }

        $ids =  array_merge($ids, $query->posts);

        return count($ids) > 0 ? array_unique($ids) : [];
    }

    /**
     * Gets the IDs of campaigns associated with a given user ID.
     *
     * @param int $user_id The ID of the user to retrieve all campaigns for. If null is passed, campaigns for all users will be retrieved.
     * @return array An array of IDs of campaigns associated with the user. If no campaigns are found, an empty array is returned.
     */
    public function get_all_campaign_ids_by_user($user_id = null)
    {
        $query_args = [
            'post_type' => Campaign::NAME,
            'post_status' => 'any',
            'fields' => 'ids',
            'posts_per_page' => -1,
        ];

        $ids = [];

        if ($user_id) {
            $query_args['author'] = $user_id;
            $ids = $this->get_campaign_ids_by_collaborator($user_id) ?? [];
        }

        $query = new WP_Query($query_args);

        if (is_wp_error($query)) {
            throw new Exception(esc_html($query->get_error_message()));
        }

        $ids =  array_merge($ids, $query->posts);

        return count($ids) > 0 ? array_unique($ids) : [];
    }

    /**
     * Check if the user is a collaborator of the campaign.
     *
     * @param int $user_id
     * @param int $campaign_id
     * @return boolean
     */
    public function is_collaborator($user_id, $campaign_id)
    {
        $collaborators = $this->get_collaborators_by_id($campaign_id);

        return in_array((int) $user_id, $collaborators, true);
    }

    /**
     * Get the author ID associated with a given campaign ID.
     *
     * @param int $campaign_id The ID of the campaign to retrieve the author ID for.
     * @return int The ID of the author associated with the campaign.
     */
    public function get_author_id($campaign_id)
    {
        return (int) get_post_field('post_author', $campaign_id) ?? 0;
    }

    /**
     * Get successful campaigns count.
     * We are calling a campaign successful if the campaign status is funded or completed.
     * 
     * @param string|null $start_date The start date in 'Y-m-d' format. Optional.
     * @param string|null $end_date The end date in 'Y-m-d' format. Optional.
     * 
     * @return int
     */
    public function get_count_of_successful_campaigns($start_date = null, $end_date = null)
    {
        $start_date = !empty($start_date) ? new DateTime($start_date) : null;
        $end_date = !empty($end_date) ? new DateTime($end_date) : null;

        $query = QueryBuilder::query()->table(WP::POSTS_TABLE . ' as campaigns')
            ->join_raw(
                WP::POST_META_TABLE . ' as campaign_status_meta',
                'INNER',
                sprintf("campaigns.ID = campaign_status_meta.post_id AND campaign_status_meta.meta_key = '%s'", growfund_with_prefix('status'))
            )->where_in('campaign_status_meta.meta_value', [CampaignStatus::COMPLETED, CampaignStatus::FUNDED]);

        if (growfund_user()->is_fundraiser()) {
            $campaign_ids = growfund_get_all_campaign_ids_by_fundraiser();
            $query->where_in('campaigns.ID', $campaign_ids);
        }


        if ($start_date !== null) {
            $query->where_date('campaigns.post_date', '>=', $start_date->format(DateTimeFormats::DB_DATE));
        }

        if ($end_date !== null) {
            $query->where_date('campaigns.post_date', '<=', $end_date->format(DateTimeFormats::DB_DATE));
        }

        $count = $query->count();

        return $count ? (int) $count : 0;
    }

    /**
     * Get successful campaigns count for fundraiser.
     * We are calling a campaign successful if the campaign status is funded or completed.
     * 
     * @param int $fundraiser_id
     * 
     * @return int
     */
    public function get_count_of_successful_campaigns_for_fundraiser(int $fundraiser_id)
    {
        $campaign_collaborators_table = QueryBuilder::query()->table(Tables::CAMPAIGN_COLLABORATORS)->get_table_name();

        $count = QueryBuilder::query()->table(WP::POSTS_TABLE . ' as campaigns')
            ->join_raw(
                WP::POST_META_TABLE . ' as campaign_status_meta',
                'INNER',
                sprintf("campaigns.ID = campaign_status_meta.post_id AND campaign_status_meta.meta_key = '%s'", growfund_with_prefix('status'))
            )->where_in('campaign_status_meta.meta_value', [CampaignStatus::COMPLETED, CampaignStatus::FUNDED])
            ->where_raw(sprintf(
                "(campaigns.post_author = %s OR EXISTS ( SELECT 1 FROM `%s` AS collaborators WHERE collaborators.campaign_id = campaigns.ID AND collaborators.collaborator_id = %s))",
                $fundraiser_id,
                $campaign_collaborators_table,
                $fundraiser_id
            ))->count();

        return $count ? (int) $count : 0;
    }

    /**
     * Get total campaigns count.
     * All campaigns except trashed.
     * 
     * @param string|null $start_date The start date in 'Y-m-d' format. Optional.
     * @param string|null $end_date The end date in 'Y-m-d' format. Optional.
     * 
     * @return int
     */
    public function get_count_of_all_campaigns($start_date = null, $end_date = null)
    {
        $start_date = !empty($start_date) ? new DateTime($start_date) : null;
        $end_date = !empty($end_date) ? new DateTime($end_date) : null;

        $query = QueryBuilder::query()->table(WP::POSTS_TABLE . ' as campaigns')
            ->join_raw(
                WP::POST_META_TABLE . ' as campaign_status_meta',
                'INNER',
                sprintf("campaigns.ID = campaign_status_meta.post_id AND campaign_status_meta.meta_key = '%s'", growfund_with_prefix('status'))
            )->where('campaign_status_meta.meta_value', '!=', CampaignStatus::TRASHED);

        if ($start_date !== null) {
            $query->where_date('campaigns.post_date', '>=', $start_date->format(DateTimeFormats::DB_DATE));
        }

        if ($end_date !== null) {
            $query->where_date('campaigns.post_date', '<=', $end_date->format(DateTimeFormats::DB_DATE));
        }

        $count = $query->count();

        return $count ? (int) $count : 0;
    }


    /**
     * Get failed campaigns count.
     * The campaign is failed if the campaign is published and end date is defined and goal doesn't met or marked as cancelled.
     * 
     * @param string|null $start_date The start date in 'Y-m-d' format. Optional.
     * @param string|null $end_date The end date in 'Y-m-d' format. Optional.
     * 
     * @return int
     */
    public function get_count_of_failed_campaigns($start_date = null, $end_date = null)
    {
        $start_date = !empty($start_date) ? new DateTime($start_date) : null;
        $end_date = !empty($end_date) ? new DateTime($end_date) : null;

        $query = QueryBuilder::query()->table(WP::POSTS_TABLE . ' as campaigns')
            ->join_raw(
                WP::POST_META_TABLE . ' as campaign_status_meta',
                'INNER',
                sprintf("campaigns.ID = campaign_status_meta.post_id AND campaign_status_meta.meta_key = '%s'", growfund_with_prefix('status'))
            )->join_raw(
                WP::POST_META_TABLE . ' as campaign_end_date_meta',
                'INNER',
                sprintf("campaigns.ID = campaign_end_date_meta.post_id AND campaign_end_date_meta.meta_key = '%s'", growfund_with_prefix('end_date'))
            )->where_in('campaign_status_meta.meta_value', [CampaignStatus::PUBLISHED,  CampaignStatus::CANCELLED]);

        if (growfund_user()->is_fundraiser()) {
            $campaign_ids = growfund_get_all_campaign_ids_by_fundraiser();
            $query->where_in('campaigns.ID', $campaign_ids);
        }

        if ($start_date !== null) {
            $query->where_date('campaigns.post_date', '>=', $start_date->format(DateTimeFormats::DB_DATE));
        }

        if ($end_date !== null) {
            $query->where_date('campaigns.post_date', '<=', $end_date->format(DateTimeFormats::DB_DATE));
        }

        $count = $query->where_raw(sprintf(
            "(( campaign_end_date_meta.meta_value IS NOT NULL AND DATE(campaign_end_date_meta.meta_value) < DATE(now()) ) 
            OR campaign_status_meta.meta_value = '%s')",
            CampaignStatus::CANCELLED
        ))->count();

        return $count ? (int) $count : 0;
    }

    /**
     * Get failed campaigns count for fundraiser.
     * The campaign is failed if the campaign is published and end date is defined and goal doesn't met or marked as cancelled.
     * 
     * @param int fundraiser_id
     * 
     * @return int
     */
    public function get_count_of_failed_campaigns_for_fundraiser(int $fundraiser_id)
    {
        $campaign_collaborators_table = QueryBuilder::query()->table(Tables::CAMPAIGN_COLLABORATORS)->get_table_name();

        $count = QueryBuilder::query()->table(WP::POSTS_TABLE . ' as campaigns')
            ->join_raw(
                WP::POST_META_TABLE . ' as campaign_status_meta',
                'INNER',
                sprintf("campaigns.ID = campaign_status_meta.post_id AND campaign_status_meta.meta_key = '%s'", growfund_with_prefix('status'))
            )->join_raw(
                WP::POST_META_TABLE . ' as campaign_end_date_meta',
                'INNER',
                sprintf("campaigns.ID = campaign_end_date_meta.post_id AND campaign_end_date_meta.meta_key = '%s'", growfund_with_prefix('end_date'))
            )->where_in('campaign_status_meta.meta_value', [CampaignStatus::PUBLISHED,  CampaignStatus::CANCELLED])
            ->where_raw(sprintf(
                "(( campaign_end_date_meta.meta_value IS NOT NULL AND DATE(campaign_end_date_meta.meta_value) < DATE(now()) ) 
                OR campaign_status_meta.meta_value = '%s')",
                CampaignStatus::CANCELLED
            ))->where_raw(sprintf(
                "(campaigns.post_author = %s OR EXISTS ( SELECT 1 FROM `%s` AS collaborators WHERE collaborators.campaign_id = campaigns.ID AND collaborators.collaborator_id = %s))",
                $fundraiser_id,
                $campaign_collaborators_table,
                $fundraiser_id
            ))->count();

        return $count ? (int) $count : 0;
    }

    /**
     * Update the secondary status of a campaign.
     *
     * @param integer $campaign_id
     * @param string<ended|hidden|visible|pause|resume> $status
     * @return int|bool
     */
    public function update_secondary_status(int $campaign_id, string $status)
    {
        switch ($status) {
            case CampaignSecondaryStatus::END:
                $this->mark_campaign_as_ended($campaign_id);
                break;
            case CampaignSecondaryStatus::HIDE:
            case CampaignSecondaryStatus::VISIBLE:
                $this->change_campaign_visibility_status($campaign_id, $status);
                break;
            case CampaignSecondaryStatus::PAUSE:
            case CampaignSecondaryStatus::RESUME:
                $this->change_campaign_pause_status($campaign_id, $status);
        }
    }

    /**
     * Change the campaign pause/resume status
     *
     * @param integer $campaign_id
     * @param string<pause|resume> $status
     *
     * @return int|bool
     */
    protected function change_campaign_pause_status(int $campaign_id, string $status)
    {
        $status = $status === CampaignSecondaryStatus::PAUSE ? true : false;

        return PostMeta::update($campaign_id, 'is_paused', $status);
    }

    /**
     * Change the campaign visibility status
     *
     * @param integer $campaign_id
     * @param string<hidden|visible> $status
     *
     * @return int|bool
     */
    protected function change_campaign_visibility_status(int $campaign_id, string $status)
    {
        $status = $status === CampaignSecondaryStatus::HIDE ? true : false;

        return PostMeta::update($campaign_id, 'is_hidden', $status);
    }

    /**
     * Mark a campaign as ended forcefully mainly by an admin.
     *
     * @param integer $campaign_id
     * @return int|bool
     */
    protected function mark_campaign_as_ended(int $campaign_id)
    {
        $is_updated = PostMeta::update($campaign_id, 'is_ended', true);

        if (!$is_updated) {
            return false;
        }

        growfund_event(new CampaignEndedEvent($campaign_id));

        return $is_updated;
    }
}
