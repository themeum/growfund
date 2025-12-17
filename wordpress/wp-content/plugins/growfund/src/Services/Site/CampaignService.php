<?php

namespace Growfund\Services\Site;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\Status\CampaignStatus;
use Growfund\Constants\Site\Campaign as CampaignConstants;
use Growfund\DTO\PaginatedCollectionDTO;
use Growfund\DTO\Site\Campaign\CampaignUpdateDTO;
use Growfund\DTO\Site\Campaign\CampaignCardDTO;
use Growfund\DTO\Site\Campaign\CampaignDetailsDTO;
use Growfund\DTO\Site\Campaign\CampaignUpdateDetailsDTO;
use Growfund\DTO\Site\Campaign\CampaignFiltersDTO;
use Growfund\DTO\Site\OrderConfirmationDTO;
use Growfund\PostTypes\Campaign;
use Growfund\PostTypes\CampaignPost;
use Growfund\QueryBuilder;
use Growfund\Services\DonationService;
use Growfund\Services\PledgeService;
use Growfund\Services\Site\RewardService;
use Growfund\Services\Site\CommentService;
use Growfund\Services\CampaignCategoryService;
use Growfund\Supports\Arr;
use Growfund\Supports\MediaAttachment;
use Growfund\Supports\PostMeta;
use Growfund\Supports\Pagination as PaginationSupport;
use Growfund\Supports\Terms;
use Growfund\Supports\UserMeta;
use Growfund\Supports\Utils;
use Growfund\Taxonomies\Category;
use Growfund\Taxonomies\Tag;
use Exception;
use Growfund\Constants\Campaign\ReachingAction;
use WP_Post;
use WP_Query;
use Growfund\Constants\DateTimeFormats;
use Growfund\Constants\Site\Visibility;
use Growfund\DTO\Site\Comment\CommentFormDTO;
use Growfund\DTO\Site\Comment\CommentDTO;
use Growfund\DTO\Site\Campaign\CampaignAuthorDTO;
use Growfund\DTO\Site\Campaign\CampaignTemplateDTO;
use Growfund\DTO\Donation\DonationFilterParamsDTO;
use Growfund\Constants\Status\DonationStatus;
use Growfund\Constants\Status\PledgeStatus;
use Growfund\Constants\Tables;
use Growfund\Core\AppSettings;
use Growfund\Exceptions\NotFoundException;
use Growfund\Sanitizer;
use Growfund\Services\Site\CampaignTabService;
use Growfund\Services\BookmarkService;
use Growfund\Services\CampaignService as APICampaignService;
use Growfund\Supports\CampaignGoal;
use Growfund\Supports\Date;
use Growfund\Supports\MetaQueryFilter;
use Growfund\Supports\WPQueryHelper;

class CampaignService
{

    protected $pledge_service;
    protected $donation_service;
    protected $reward_service;
    protected $comment_service;
    protected $category_service;
    protected $bookmark_service;
    protected $campaign_settings;

    public function __construct()
    {
        $this->pledge_service = new PledgeService();
        $this->donation_service = new DonationService();
        $this->reward_service = new RewardService();
        $this->comment_service = new CommentService();
        $this->category_service = new CampaignCategoryService();
        $this->bookmark_service = new BookmarkService();
        $this->campaign_settings = growfund_settings(AppSettings::CAMPAIGNS);
    }

    /**
     * Get campaigns with pagination.
     *
     * @param CampaignFiltersDTO $filters_dto
     * @return PaginatedCollectionDTO
     */
    public function paginated(CampaignFiltersDTO $filters_dto)
    {
        $query_args = $this->build_campaign_query_args($filters_dto);
        add_filter('posts_where', [new MetaQueryFilter(), 'update_meta_value_is_null']);
        $query = new WP_Query($query_args);
        remove_filter('posts_where', [new MetaQueryFilter(), 'update_meta_value_is_null']);

        if (!$query->have_posts()) {
            return $this->create_empty_paginated_result($filters_dto->limit, $filters_dto->page);
        }

        $results = Arr::make($query->posts)
            ->map(function ($campaign) {
                return $this->prepare_campaign_card_data($campaign);
            })
            ->toArray();

        return $this->create_paginated_result($results, $query, $filters_dto);
    }

    /**
     * Get campaign updates with pagination.
     *
     * @param int $campaign_id
     * @param int $page
     * @param int $limit
     * @return PaginatedCollectionDTO
     */
    public function get_campaign_updates_paginated(int $campaign_id, int $page = CampaignConstants::DEFAULT_PAGE, int $limit = CampaignConstants::DEFAULT_UPDATES_LIMIT)
    {
        if (!$this->is_valid_campaign_id($campaign_id)) {
            return $this->create_empty_paginated_result($limit, $page);
        }

        $args = $this->build_campaign_posts_query_args($campaign_id, $limit, $page);
        $query = new WP_Query($args);

        if (!$this->is_valid_query($query)) {
            return $this->create_empty_paginated_result($limit, $page);
        }

        $updates = $this->extract_updates_from_query($query, $page, $limit);
        wp_reset_postdata();

        return $this->create_updates_paginated_result($query, $updates, $page, $limit);
    }

    /**
     * Create updates paginated result.
     *
     * @param WP_Query $query
     * @param array $updates
     * @param int $page
     * @param int $limit
     * @return PaginatedCollectionDTO
     */
    protected function create_updates_paginated_result(WP_Query $query, array $updates, int $page, int $limit)
    {
        $updates_dto = new PaginatedCollectionDTO();
        $updates_dto->results = $updates;
        $updates_dto->has_more = $query->max_num_pages > $page;
        $updates_dto->total = $query->found_posts;
        $updates_dto->current_page = $page;
        $updates_dto->per_page = $limit;
        $updates_dto->count = count($updates);
        $updates_dto->overall = $query->found_posts;
        return $updates_dto;
    }

    /**
     * Get total count of campaign updates.
     *
     * @param int $campaign_id
     * @return int
     */
    public function get_campaign_updates_count(int $campaign_id)
    {
        $args = $this->build_campaign_posts_query_args($campaign_id, CampaignConstants::NO_LIMIT, CampaignConstants::DEFAULT_PAGE, 'date', 'DESC', 'ids');
        $query = new WP_Query($args);
        return $query->found_posts;
    }

    /**
     * Get a list of related campaigns in the same category.
     *
     * @param int|null $category_id
     * @param int $limit
     * @return CampaignCardDTO[]
     */
    public function get_related_campaigns($category_id, int $limit = CampaignConstants::DEFAULT_RELATED_CAMPAIGNS_LIMIT)
    {
        if (empty($category_id)) {
            return [];
        }

        $args = $this->build_related_campaigns_query_args($category_id, $limit);
        $campaigns = get_posts($args);

        if (empty($campaigns)) {
            return [];
        }

        return Arr::make($campaigns)
            ->map(function ($campaign) {
                return $this->prepare_campaign_card_data($campaign);
            })
            ->toArray();
    }

    /**
     * Get single update data with prepared comment information.
     *
     * @param int $campaign_id
     * @param string $update_id
     * @return CampaignUpdateDetailsDTO|null
     */
    public function get_single_update_data(int $campaign_id, string $update_id)
    {
        if (!$this->is_valid_campaign_id($campaign_id) || !$this->is_valid_update_id($update_id)) {
            return null;
        }

        $post = $this->find_update_post($campaign_id, $update_id);

        if (!$this->is_valid_post($post)) {
            return null;
        }

        $update_data = $this->build_single_update_data($post);

        if (!$this->is_valid_update_data($update_data)) {
            return null;
        }

        return $this->build_update_details_dto($update_data);
    }

    /**
     * Guard method to validate campaign ID.
     *
     * @param int $campaign_id
     * @return bool
     */
    protected function is_valid_campaign_id(int $campaign_id)
    {
        return $campaign_id > 0;
    }

    /**
     * Guard method to validate update ID.
     *
     * @param string $update_id
     * @return bool
     */
    protected function is_valid_update_id(string $update_id)
    {
        return !empty($update_id) && is_string($update_id);
    }

    /**
     * Guard method to validate update data.
     *
     * @param mixed $update_data
     * @return bool
     */
    protected function is_valid_update_data($update_data)
    {
        return $update_data !== null && is_object($update_data);
    }

    /**
     * Build update details DTO from update data.
     *
     * @param mixed $update_data
     * @return CampaignUpdateDetailsDTO
     */
    protected function build_update_details_dto($update_data)
    {
        $update_details_dto = new CampaignUpdateDetailsDTO();

        $update_details_dto->id = $update_data->id ?? 0;
        $update_details_dto->title = $update_data->title ?? '';
        $update_details_dto->description = $update_data->content_full ?? '';
        $update_details_dto->slug = $update_data->update_id ?? '';
        $update_details_dto->thumbnail = $update_data->image ?? null;
        $update_details_dto->comment_form_data = $update_data->comment_form_data ?? null;
        $update_details_dto->comments = $update_data->comments ?? [];
        $update_details_dto->total_comments_count = count($update_data->comments ?? []);
        $update_details_dto->author_name = $update_data->creator['name'] ?? null;
        $update_details_dto->author_image = $update_data->creator['avatar'] ?? null;

        $update_details_dto->update_id = $update_data->update_id ?? null;
        $update_details_dto->update_title = $update_data->title ?? null;
        $update_details_dto->update_date = $update_data->date ?? null;
        $update_details_dto->update_image = $update_data->image ?? null;
        $update_details_dto->update_image_alt = $update_data->image_alt ?? null;
        $update_details_dto->update_content_preview = $update_data->content_preview ?? null;
        $update_details_dto->update_content_full = $update_data->content_full ?? null;
        $update_details_dto->update_position = $update_data->position ?? null;
        $update_details_dto->update_creator = $update_data->creator ?? [];
        $update_details_dto->update_stats = $update_data->stats ?? [];
        $update_details_dto->update_badge = $update_data->badge ?? null;

        return $update_details_dto;
    }

    /**
     * Build query arguments for campaign listing.
     *
     * @param CampaignFiltersDTO $filters_dto
     * @return array
     */
    protected function build_campaign_query_args(CampaignFiltersDTO $filters_dto)
    {
        $query_args = [
            'post_type'      => Campaign::NAME,
            'posts_per_page' => $filters_dto->limit,
            'paged'          => $filters_dto->page,
            's'              => $filters_dto->search,
            'no_found_rows'  => false,
        ];

        $this->apply_filters_to_query($query_args, $filters_dto);
        $this->apply_sorting_to_query($query_args, $filters_dto->sort);

        return $query_args;
    }

    /**
     * Apply filters to query arguments.
     *
     * @param array $query_args
     * @param CampaignFiltersDTO $filters_dto
     */
    protected function apply_filters_to_query(array &$query_args, CampaignFiltersDTO $filters_dto)
    {
        $this->apply_status_filter($query_args, $filters_dto);
        $this->apply_date_filters($query_args, $filters_dto);
        $this->apply_taxonomy_filters($query_args, $filters_dto);
        $this->apply_featured_filter($query_args, $filters_dto);
        $this->normalize_query_relations($query_args);
    }

    /**
     * Apply status filter.
     *
     * @param array $query_args
     * @param CampaignFiltersDTO $filters_dto
     */
    protected function apply_status_filter(array &$query_args, CampaignFiltersDTO $filters_dto)
    {
        if (!empty($filters_dto->status)) {
            $query_args['meta_query'][] = [
                'key'     => growfund_with_prefix('status'),
                'value'   => $filters_dto->status,
                'compare' => 'IN'
            ];
        }
    }

    /**
     * Apply date filters.
     *
     * @param array $query_args
     * @param CampaignFiltersDTO $filters_dto
     */
    protected function apply_date_filters(array &$query_args, CampaignFiltersDTO $filters_dto)
    {
        if (!empty($filters_dto->start_date)) {
            $query_args['meta_query'][] = [
                'key'     => growfund_with_prefix('start_date'),
                'value'   => $filters_dto->start_date,
                'compare' => '>=',
                'type'    => 'DATE'
            ];
        }

        if (!empty($filters_dto->end_date)) {
            $query_args['meta_query'][] = [
                'key'     => growfund_with_prefix('end_date'),
                'value'   => $filters_dto->end_date,
                'compare' => '<=',
                'type'    => 'DATE'
            ];
        }

        if ($filters_dto->only_active === true) {
            $query_args['meta_query'][] = [
                'key'     => growfund_with_prefix('start_date'),
                'value'   => Date::current_sql_safe(),
                'compare' => '<=',
                'type'    => 'DATE'
            ];
            $query_args['meta_query'][] = [
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
			];
            $query_args['meta_query'][] = [
                'relation' => 'OR',
				[
					'key'     => growfund_with_prefix('is_hidden'),
					'value'   => 1,
					'compare' => '!=',
				],
				[
					'key'     => growfund_with_prefix('is_hidden'),
					'compare' => 'NOT EXISTS',
				],
            ];
        }
    }

    /**
     * Apply taxonomy filters.
     *
     * @param array $query_args
     * @param CampaignFiltersDTO $filters_dto
     */
    protected function apply_taxonomy_filters(array &$query_args, CampaignFiltersDTO $filters_dto)
    {
        if (!empty($filters_dto->category)) {
            $this->apply_category_filter($query_args, $filters_dto->category);
        }

        if (!empty($filters_dto->tag)) {
            $query_args['tax_query'][] = [
                'taxonomy' => Tag::NAME,
                'field'    => 'slug',
                'terms'    => $filters_dto->tag,
            ];
        }
    }

    /**
     * Apply category filter with children inclusion.
     *
     * @param array $query_args
     * @param string $category
     */
    protected function apply_category_filter(array &$query_args, string $category)
    {
        $term = get_term_by('slug', $category, Category::NAME);

        if ($term && !is_wp_error($term)) {
            $tax_query = [
                'taxonomy' => Category::NAME,
                'field'    => 'slug',
                'terms'    => $category,
            ];

            if ((int) $term->parent === CampaignConstants::DEFAULT_TOP_LEVEL_PARENT && !empty(get_term_children($term->term_id, Category::NAME))) {
                $tax_query['include_children'] = true;
            }

            $query_args['tax_query'][] = $tax_query;
        }
    }

    /**
     * Apply featured filter.
     *
     * @param array $query_args
     * @param CampaignFiltersDTO $filters_dto
     */
    protected function apply_featured_filter(array &$query_args, CampaignFiltersDTO $filters_dto)
    {
        if (isset($filters_dto->featured) && $filters_dto->featured === true) {
            $query_args['meta_query'][] = [
                'key'     => growfund_with_prefix('is_featured'),
                'value'   => CampaignConstants::FEATURED_STATUS_VALUE,
                'compare' => '='
            ];
        }
    }

    /**
     * Normalize query relations.
     *
     * @param array $query_args
     */
    protected function normalize_query_relations(array &$query_args)
    {
        if (count($query_args['meta_query'] ?? []) > CampaignConstants::MIN_QUERY_COUNT_FOR_RELATION) {
            $query_args['meta_query']['relation'] = 'AND';
        }

        if (count($query_args['tax_query'] ?? []) > CampaignConstants::MIN_QUERY_COUNT_FOR_RELATION) {
            $query_args['tax_query']['relation'] = 'AND';
        }
    }

    /**
     * Apply sorting to query arguments.
     *
     * @param array $query_args
     * @param mixed $sort
     */
    protected function apply_sorting_to_query(array &$query_args, $sort)
    {
        if (empty($sort)) {
            $query_args['orderby'] = 'meta_value';
            $query_args['meta_key'] = growfund_with_prefix('start_date'); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
            $query_args['order'] = 'DESC';
            $query_args['meta_type'] = 'DATE'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
            return;
        }

        $sorting_config = $this->get_sorting_config($sort);
        $query_args = array_merge($query_args, $sorting_config);
    }

    /**
     * Get sorting configuration.
     *
     * @param mixed $sort
     * @return array
     */
    protected function get_sorting_config($sort)
    {
        switch ($sort) {
            case 'newest':
                return [
                    'orderby' => 'meta_value',
                    'meta_key' => growfund_with_prefix('start_date'), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
                    'order' => 'DESC',
                    'meta_type' => 'DATE' // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
                ];
            case 'end_date':
                return [
                    'orderby' => 'meta_value',
                    'meta_key' => growfund_with_prefix('end_date'), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
                    'order' => 'ASC',
                    'meta_type' => 'DATE' // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
                ];
            case 'latest':
                return [
                    'orderby' => 'date',
                    'order' => 'DESC'
                ];
            case 'popular':
                return [
                    'orderby' => 'comment_count',
                    'order' => 'DESC'
                ];
            case 'goal_asc':
                return [
                    'orderby' => 'meta_value_num',
                    'meta_key' => growfund_with_prefix('goal_amount'), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
                    'order' => 'ASC'
                ];
            case 'goal_desc':
                return [
                    'orderby' => 'meta_value_num',
                    'meta_key' => growfund_with_prefix('goal_amount'), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
                    'order' => 'DESC'
                ];
            case 'most_funded':
                return [
                    'orderby' => 'meta_value_num',
                    'meta_key' => growfund_with_prefix('total_fund_raised'), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
                    'order' => 'DESC'
                ];
            case 'most_backed':
                return [
                    'orderby' => 'meta_value_num',
                    'meta_key' => growfund_with_prefix('total_backers'), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
                    'order' => 'DESC'
                ];
            case 'near_me':
                return [
                    'orderby' => 'date',
                    'order' => 'DESC'
                ];
            default:
                return [
                    'orderby' => 'meta_value',
                    'meta_key' => growfund_with_prefix('start_date'), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
                    'order' => 'DESC',
                    'meta_type' => 'DATE'
                ];
        }
    }

    /**
     * Build query arguments for related campaigns.
     *
     * @param int $category_id
     * @param int $limit
     * @return array
     */
    protected function build_related_campaigns_query_args(int $category_id, int $limit)
    {
        return [
            'post_type'      => Campaign::NAME,
            'posts_per_page' => $limit,
            'orderby'        => 'rand',
            'order'          => 'DESC',
            'meta_query'     => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
                [
                    'key'     => growfund_with_prefix('status'),
                    'value'   => CampaignStatus::PUBLISHED,
                    'compare' => '='
                ],
            ],
            'tax_query'      => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
                [
                    'taxonomy' => Category::NAME,
                    'field'    => 'id',
                    'terms'    => $category_id,
                ],
            ],
            'meta_key'   => growfund_with_prefix('status'), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
            'meta_value' => CampaignStatus::PUBLISHED, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
        ];
    }

    /**
     * Build common query arguments for campaign posts.
     *
     * @param int $campaign_id
     * @param int $posts_per_page
     * @param int $page
     * @param string $orderby
     * @param string $order
     * @param string $fields
     * @return array
     */
    protected function build_campaign_posts_query_args(
        int $campaign_id,
        int $posts_per_page = CampaignConstants::DEFAULT_UPDATES_LIMIT,
        int $page = CampaignConstants::DEFAULT_PAGE,
        string $orderby = 'date',
        string $order = 'DESC',
        string $fields = 'all'
    ) {
        $args = [
            'post_type'      => CampaignPost::NAME,
            'posts_per_page' => $posts_per_page,
            'orderby'        => $orderby,
            'order'          => $order,
            'post_parent'    => $campaign_id,
            'no_found_rows'  => false,
            'cache_results'  => false,
        ];

        if ($page > CampaignConstants::DEFAULT_PAGE) {
            $args['paged'] = $page;
        }

        if ($fields !== 'all') {
            $args['fields'] = $fields;
        }

        return $args;
    }

    /**
     * Prepare the campaign data into the expected format.
     *
     * @param WP_Post $campaign
     * @return CampaignCardDTO
     */
    protected function prepare_campaign_card_data(WP_Post $campaign)
    {
        $campaign_data = $this->extract_campaign_data($campaign);
        $categories = $this->extract_campaign_categories($campaign->ID);
        $is_featured = $this->get_featured_status($campaign->ID);

        $campaign_data->category_id = $categories['category_id'];
        $campaign_data->subcategory_id = $categories['subcategory_id'];
        $campaign_data->is_featured = $is_featured;

        $current_user_id = growfund_user()->get_id();

        if ($current_user_id > 0) {
            $campaign_data->is_bookmarked = $this->bookmark_service->is_bookmarked($current_user_id, $campaign->ID);
        } else {
            $campaign_data->is_bookmarked = false;
        }

        return $campaign_data;
    }

    /**
     * Prepare the campaign details data into the expected format.
     *
     * @param int $campaign_id
     * @return CampaignDetailsDTO
     */
    protected function prepare_campaign_details_data($campaign_id)
    {
        $campaign = get_post($campaign_id);

        if (!$campaign) {
            throw new NotFoundException(esc_html__('Campaign not found.', 'growfund'));
        }

        $base_data = $this->extract_campaign_data($campaign);
        $meta = PostMeta::get_all($campaign->ID);

        $campaign_details_dto = new CampaignDetailsDTO();
        $campaign_details_dto->id = $campaign->ID;
        $campaign_details_dto->title = $base_data->title;
        $campaign_details_dto->description = $base_data->description;
        $campaign_details_dto->thumbnail = $this->get_campaign_thumbnail($meta);
        $campaign_details_dto->start_date = $base_data->start_date;
        $campaign_details_dto->end_date = $base_data->end_date;
        $campaign_details_dto->author = $this->extract_author_data($campaign->post_author);
        $campaign_details_dto->goal_amount = $base_data->goal_amount;
        $campaign_details_dto->fund_raised = $base_data->fund_raised;
        $campaign_details_dto->video = $this->prepare_campaign_video($meta);
        $campaign_details_dto->images = $this->prepare_campaign_images($campaign->ID, $meta['images'] ?? null);
        $campaign_details_dto->tags = $this->prepare_campaign_tags($campaign->ID);
        $campaign_details_dto->slug = $campaign->post_name;

        if (growfund_app()->is_donation_mode()) {
            $campaign_details_dto->number_of_contributors = $this->donation_service->get_total_donors_count_for_campaign($campaign->ID);
            $campaign_details_dto->number_of_contributions = $this->donation_service->get_total_count_of_donations_for_campaign($campaign->ID);
        } else {
            $campaign_details_dto->allow_pledge_without_reward = $meta['allow_pledge_without_reward'] ?? false;
            $campaign_details_dto->min_pledge_amount = $meta['min_pledge_amount'];
            $campaign_details_dto->max_pledge_amount = $meta['max_pledge_amount'];
            $campaign_details_dto->number_of_contributors = $this->pledge_service->get_total_backers_count_for_campaign($campaign->ID);
            $campaign_details_dto->number_of_contributions = $this->pledge_service->get_total_number_of_pledges_for_campaign($campaign->ID, true);
        }

        $campaign_details_dto->has_goal = filter_var($meta['has_goal'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $campaign_details_dto->goal_type = $meta['goal_type'] ?? null;
        $campaign_details_dto->location = $meta['location'] ?? null;
        $campaign_details_dto->story = $meta['story'] ?? null;
        $campaign_details_dto->appreciation_type = $meta['appreciation_type'] ?? null;
        $campaign_details_dto->rewards = $this->get_campaign_rewards($campaign->ID);
        $campaign_details_dto->campaign_updates = $this->prepare_campaign_updates($campaign->ID);
        $campaign_details_dto->total_campaign_updates_count = $this->get_campaign_updates_count($campaign->ID);
        $campaign_details_dto->total_comments_count = $this->get_campaign_total_comments_count($campaign->ID);
        $campaign_details_dto->related_campaigns = $this->prepare_related_campaigns($campaign->ID);
        $campaign_details_dto->comments = $this->get_campaign_comments($campaign->ID);
        $campaign_details_dto->comment_form_data = $this->get_comment_form_data($campaign->ID);
        $faqs = PostMeta::get($campaign->ID, 'faqs');
        $campaign_details_dto->faqs = !empty($faqs)
            ? maybe_unserialize($faqs)
            : [];

        if (growfund_app()->is_donation_mode()) {
            $campaign_details_dto->campaign_donations = $this->get_campaign_donations($campaign->ID);
        }

        $campaign_details_dto->collaborators = $meta['show_collaborator_list'] ? $this->get_collaborators_by_campaign_id($campaign->ID) : [];
        $campaign_details_dto->checkout_url = Utils::get_checkout_url($campaign->ID);

        $is_ended = !empty($campaign_details_dto->end_date)
            ? Date::is_date_in_past($campaign_details_dto->end_date)
            : false;

        $campaign_details_dto->is_ended = $is_ended || boolval($meta['is_ended'] ?? false);

        return $campaign_details_dto;
    }

    public function get_collaborators_by_campaign_id($campaign_id)
    {
        $ids = $this->get_collaborator_ids_by_campaign_id($campaign_id);

        $collaborators = [];

        foreach ($ids as $id) {
            $collaborators[] = $this->extract_author_data($id);
        }

        return $collaborators;
    }
    
    public function get_collaborator_ids_by_campaign_id($campaign_id)
    {
        $records = QueryBuilder::query()->table(Tables::CAMPAIGN_COLLABORATORS)->select(['collaborator_id'])->where('campaign_id', $campaign_id)->get();
        $ids = wp_list_pluck($records, 'collaborator_id');

        return $ids ?? [];
    }

    /**
     * Prepare campaign video data.
     *
     * @param array $meta
     * @return array|null
     */
    protected function prepare_campaign_video(array $meta)
    {
        return MediaAttachment::make_video($meta['video'] ?? null);
    }

    /**
     * Prepare campaign images data.
     *
     * @param array|null $images
     * @param int $campaign_id
     * @return array|null
     */
    protected function prepare_campaign_images(int $campaign_id, $images = null)
    {
        // First try to get the featured image (WordPress native way)
        $featured_image_id = get_post_thumbnail_id($campaign_id);

        if ($featured_image_id) {
            $featured_image = MediaAttachment::make($featured_image_id);
            return [$featured_image];
        }

        // Fallback to custom meta images if no featured image
        if (!empty($images) && is_array($images)) {
            return MediaAttachment::make_many($images);
        }

        return null;
    }

    /**
     * Prepare campaign tags.
     *
     * @param int $campaign_id
     * @return array
     */
    protected function prepare_campaign_tags(int $campaign_id)
    {
        return Terms::get_terms($campaign_id, Tag::NAME);
    }

    /**
     * Prepare campaign updates.
     *
     * @param int $campaign_id
     * @return array
     */
    protected function prepare_campaign_updates(int $campaign_id)
    {
        $updates_result = $this->get_campaign_updates_paginated(
            $campaign_id,
            CampaignConstants::DEFAULT_PAGE,
            CampaignConstants::DEFAULT_UPDATES_LIMIT
        );

        return $updates_result->results ?? [];
    }

    /**
     * Get campaign total comments count.
     *
     * @param int $campaign_id
     * @return int
     */
    protected function get_campaign_total_comments_count(int $campaign_id)
    {
        return $this->comment_service->get_comments_count($campaign_id, 'comment');
    }

    /**
     * Prepare related campaigns.
     *
     * @param int $campaign_id
     * @return array
     */
    protected function prepare_related_campaigns(int $campaign_id)
    {
        $category_data = $this->extract_campaign_categories($campaign_id);
        return $this->get_related_campaigns($category_data['category_id']);
    }

    /**
     * Extract common campaign data from a WP_Post object.
     *
     * @param WP_Post $campaign
     * @return CampaignCardDTO
     */
    protected function extract_campaign_data(WP_Post $campaign)
    {
        $thumbnail = $this->extract_campaign_thumbnail($campaign->ID);
        $total_fund_raised = $this->get_total_fund_raised($campaign->ID);
        $user = growfund_user($campaign->post_author);

        $campaign_data_dto = new CampaignCardDTO();
        $campaign_data_dto->id = (string) $campaign->ID;
        $campaign_data_dto->title = $campaign->post_title;
        $campaign_data_dto->description = $campaign->post_content;

        $campaign_data_dto->url = growfund_campaign_url($campaign->ID);
        $campaign_data_dto->thumbnail = $thumbnail;
        $campaign_data_dto->start_date = PostMeta::get($campaign->ID, 'start_date');
        $campaign_data_dto->end_date = PostMeta::get($campaign->ID, 'end_date');
        $campaign_data_dto->author_name = $this->get_author_display_name($user);
        $author_image = MediaAttachment::make(UserMeta::get($campaign->post_author, 'image'));
        $campaign_data_dto->author_image = $author_image['url'] ?? '';
        $campaign_data_dto->has_goal = filter_var(PostMeta::get($campaign->ID, 'has_goal'), FILTER_VALIDATE_BOOLEAN);
        $campaign_data_dto->goal_amount = PostMeta::get($campaign->ID, 'goal_amount');
        $campaign_data_dto->goal_type = PostMeta::get($campaign->ID, 'goal_type');
        $campaign_data_dto->fund_raised = $total_fund_raised;

        if (growfund_app()->is_donation_mode()) {
            $campaign_data_dto->number_of_contributors = $this->donation_service->get_total_donors_count_for_campaign($campaign->ID);
            $campaign_data_dto->number_of_contributions = $this->donation_service->get_total_count_of_donations_for_campaign($campaign->ID);
        } else {
            $campaign_data_dto->number_of_contributors = $this->pledge_service->get_total_backers_count_for_campaign($campaign->ID);
            $campaign_data_dto->number_of_contributions = $this->pledge_service->get_total_number_of_pledges_for_campaign($campaign->ID, true);
        }
        

        return $campaign_data_dto;
    }

    /**
     * Extract campaign thumbnail.
     *
     * @param int $campaign_id
     * @return string|null
     */
    protected function extract_campaign_thumbnail(int $campaign_id)
    {
        $images = PostMeta::get($campaign_id, 'images');
        $thumbnail = isset($images[CampaignConstants::DEFAULT_ARRAY_INDEX])
            ? MediaAttachment::make($images[CampaignConstants::DEFAULT_ARRAY_INDEX])
            : null;

        return isset($thumbnail) ? $thumbnail['url'] : null;
    }

    /**
     * Get author display name.
     *
     * @param mixed $user
     * @return string
     */
    protected function get_author_display_name($user)
    {
        return $user ? $user->get_display_name() : '';
    }

    /**
     * Extract category information from a campaign.
     *
     * @param int $campaign_id
     * @return array
     */
    protected function extract_campaign_categories(int $campaign_id)
    {
        $terms = Terms::get_terms($campaign_id, Category::NAME);

        if (empty($terms)) {
            return [
				'category_id' => null,
				'subcategory_id' => null
			];
        }

        $category_id = null;
        $subcategory_id = null;

        foreach ($terms as $term) {
            if ($term->parent === CampaignConstants::DEFAULT_TOP_LEVEL_PARENT) {
                $category_id = (int) $term->term_id;
            } else {
                $subcategory_id = (int) $term->term_id;
            }
        }

        return [
            'category_id' => $category_id,
            'subcategory_id' => $subcategory_id,
        ];
    }

    /**
     * Extract author data for a campaign.
     *
     * @param int $author_id
     * @return CampaignAuthorDTO
     */
    protected function extract_author_data(int $author_id)
    {
        $user = growfund_user($author_id);
        $image = MediaAttachment::make(UserMeta::get($author_id, 'image'));

        $author_dto = new CampaignAuthorDTO();
        $author_dto->name = $user ? $user->get_display_name() : '';
        $author_dto->image =  !empty($image) ? $image['url'] : '';
        $author_dto->campaign_count = $this->get_campaign_count_by_user_id($author_id);
        $author_dto->pledge_count = $this->get_author_pledge_count($author_id);

        return $author_dto;
    }

    /**
     * Get author pledge count.
     *
     * @param int $author_id
     * @return int
     */
    protected function get_author_pledge_count(int $author_id)
    {
        return $this->pledge_service->get_successfully_backed_campaigns_by_backer($author_id);
    }



    /**
     * Get update image.
     *
     * @param int $post_id
     * @return string
     */
    protected function get_update_image(int $post_id)
    {
        $image_url = get_the_post_thumbnail_url($post_id, 'full');
        if (!empty($image_url)) {
            return $image_url;
        }

        $image_url = get_the_post_thumbnail_url($post_id, 'medium');
        if (!empty($image_url)) {
            return $image_url;
        }

        $image_url = get_the_post_thumbnail_url($post_id, 'thumbnail');
        if (!empty($image_url)) {
            return $image_url;
        }

        return '';
    }



    /**
     * Get update content preview.
     *
     * @param string $content
     * @return string
     */
    protected function get_update_content_preview(string $content)
    {
        if (empty($content)) {
            return __('No content available', 'growfund');
        }

        $preview = wp_trim_words($content, CampaignConstants::DEFAULT_WORD_COUNT_PREVIEW);
        if (empty($preview)) {
            return __('No content available', 'growfund');
        }

        return $preview;
    }

    /**
     * Get update date.
     *
     * @param WP_Post $post
     * @return string
     */
    protected function get_update_date(WP_Post $post)
    {
        $date = get_the_date(DateTimeFormats::DB_DATETIME, $post);
        if (!empty($date)) {
            return $date;
        }

        return current_time(DateTimeFormats::DB_DATETIME);
    }



    /**
     * Get total fund raised for a campaign.
     *
     * @param int $campaign_id
     * @return float
     */
    protected function get_total_fund_raised(int $campaign_id)
    {
        if (growfund_app()->is_donation_mode()) {
            $amount = $this->donation_service->get_total_donated_amount($campaign_id);
            return $amount ? (float) $amount : CampaignConstants::DEFAULT_FLOAT_VALUE;
        }

        $amount = $this->pledge_service->get_total_pledges_amount_for_campaign($campaign_id);
        return $amount ? (float) $amount : CampaignConstants::DEFAULT_FLOAT_VALUE;
    }



    /**
     * Get featured status for a campaign.
     *
     * @param int $campaign_id
     * @return bool
     */
    protected function get_featured_status(int $campaign_id)
    {
        $is_featured_meta = PostMeta::get($campaign_id, 'is_featured', true);
        return filter_var($is_featured_meta, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Get campaign thumbnail.
     *
     * @param array $meta
     * @return string|null
     */
    protected function get_campaign_thumbnail(array $meta)
    {
        $thumbnail = isset($meta['images'][CampaignConstants::DEFAULT_ARRAY_INDEX]) ? MediaAttachment::make($meta['images'][CampaignConstants::DEFAULT_ARRAY_INDEX]) : null;
        return $thumbnail['url'] ?? null;
    }

    /**
     * Get campaign rewards.
     *
     * @param int $campaign_id
     * @return array
     */
    protected function get_campaign_rewards(int $campaign_id)
    {
        if (growfund_app()->is_donation_mode()) {
            return [];
        }

        $paginated_result = $this->reward_service->paginated($campaign_id, CampaignConstants::DEFAULT_PAGE, CampaignConstants::DEFAULT_REWARDS_LIMIT);
        return $paginated_result->results ?? [];
    }

    /**
     * Get campaign comments.
     *
     * @param int $campaign_id
     * @return array
     */
    protected function get_campaign_comments(int $campaign_id)
    {
        $query_dto = new CommentDTO([
            'post_id' => $campaign_id,
            'page' => CampaignConstants::DEFAULT_PAGE,
            'per_page' => CampaignConstants::DEFAULT_COMMENTS_LIMIT,
            'comment_type' => 'comment'
        ]);
        $comments_result = $this->comment_service->get_comments($query_dto);
        return $comments_result->comments ?? [];
    }

    /**
     * Get comment form data.
     *
     * @param int $campaign_id
     * @return array
     */
    protected function get_comment_form_data(int $campaign_id)
    {
        if (!$this->comment_service->can_user_see_campaign_comments($campaign_id)) {
            return [];
        }

        $comment_form_dto = new CommentFormDTO([
            'comment_post_id' => $campaign_id,
            'comment_type' => 'comment',
            'placeholder' => __('Share your thoughts, ask questions or show your support...', 'growfund'),
            'button_text' => __('Post Comment', 'growfund')
        ]);

        $comment_form_dto = $this->comment_service->prepare_comment_form_data($comment_form_dto);

        if ($comment_form_dto === null) {
            $comment_form_dto = [];
        }

        return $comment_form_dto;
    }

    /**
     * Get the number of campaigns associated with a given user ID.
     *
     * @param int $user_id
     * @return int
     */
    protected function get_campaign_count_by_user_id(int $user_id)
    {
        return (int) QueryBuilder::query()
            ->table('posts')
            ->select(['ID'])
            ->where('post_author', $user_id)
            ->where('post_type', Campaign::NAME)
            ->count();
    }

    /**
     * Find update post by slug or ID.
     *
     * @param int $campaign_id
     * @param string $update_id
     * @return WP_Post|null
     */
    protected function find_update_post(int $campaign_id, string $update_id)
    {
        $args = $this->build_campaign_posts_query_args($campaign_id, CampaignConstants::DEFAULT_POSTS_PER_PAGE_SINGLE, CampaignConstants::DEFAULT_PAGE);
        $args['name'] = $update_id;

        $query = new WP_Query($args);

        if (!$query->have_posts()) {
            $args = $this->build_campaign_posts_query_args($campaign_id, CampaignConstants::DEFAULT_POSTS_PER_PAGE_SINGLE, CampaignConstants::DEFAULT_PAGE);
            $args['p'] = intval($update_id);

            $query = new WP_Query($args);
        }

        if (!$query->have_posts()) {
            return null;
        }

        $query->the_post();
        $post = get_post();
        wp_reset_postdata();

        return $post;
    }

    /**
     * Build single update data.
     *
     * @param WP_Post $post
     * @return CampaignUpdateDTO
     */
    protected function build_single_update_data(WP_Post $post)
    {
        $query_dto = new CommentDTO([
            'post_id' => $post->ID,
            'page' => CampaignConstants::DEFAULT_PAGE,
            'per_page' => CampaignConstants::DEFAULT_COMMENTS_LIMIT,
            'comment_type' => 'update_comment'
        ]);
        $comments_result = $this->comment_service->get_comments($query_dto);

        $comment_data = [];

        if ($this->campaign_settings->allow_comments()) {
            $comment_form_dto = new CommentFormDTO([
                'comment_post_id' => $post->ID,
                'comment_type' => 'update_comment',
                'placeholder' => __('Share your thoughts, ask questions or show your support...', 'growfund'),
                'button_text' => __('Post Comment', 'growfund')
            ]);

            $comment_data = $this->comment_service->prepare_comment_form_data($comment_form_dto);

            if ($comment_data === null) {
                $comment_data = [];
            }
        }

        $single_update_dto = new CampaignUpdateDTO();

        $single_update_dto->update_id = $post->post_name;
        $single_update_dto->update_post_id = $post->ID;
        $single_update_dto->post_id = $post->ID;
        $single_update_dto->update_badge = 'UPDATE';
        $single_update_dto->update_date = get_the_date(DateTimeFormats::HUMAN_READABLE_DATE, $post);
        $single_update_dto->update_title = $post->post_title;
        $single_update_dto->update_image = $this->get_update_image($post->ID);
        $single_update_dto->update_image_alt = !empty($post->post_title) ? $post->post_title : __('Update image', 'growfund');
        $single_update_dto->update_content_preview = $this->get_update_content_preview($post->post_content);
        $single_update_dto->update_position = 0;
        $single_update_dto->update_creator = [
            'name' => UserMeta::get($post->post_author, 'display_name') ?: 'Campaign Creator', // phpcs:ignore
            'avatar' => UserMeta::get($post->post_author, 'image') ?: '', // phpcs:ignore
        ];
        $count = get_comments($this->build_comment_query_args(
            $post->ID,
            'update_comment',
            CampaignConstants::DEFAULT_PARENT_COMMENT_ID,
            'approve',
            true
        ));

        $single_update_dto->update_stats = [
            'comments' => is_numeric($count) ? (int) $count : 0,
            'likes' => CampaignConstants::DEFAULT_LIKES_COUNT
        ];
        $single_update_dto->comments = $comments_result->comments ?? [];
        $single_update_dto->comment_form_data = $comment_data;

        $single_update_dto->id = $post->post_name;
        $single_update_dto->badge = 'UPDATE';
        $single_update_dto->date = get_the_date(DateTimeFormats::HUMAN_READABLE_DATE, $post);
        $single_update_dto->title = $post->post_title;
        $single_update_dto->image = $this->get_update_image($post->ID);
        $single_update_dto->image_alt = !empty($post->post_title) ? $post->post_title : __('Update image', 'growfund');
        $single_update_dto->content_preview = $this->get_update_content_preview($post->post_content);
        $single_update_dto->content_full = $post->post_content;
        $single_update_dto->position = 0;
        $single_update_dto->creator = [
            'name' => UserMeta::get($post->post_author, 'display_name') ?: 'Campaign Creator', // phpcs:ignore
            'avatar' => UserMeta::get($post->post_author, 'image') ?: '', // phpcs:ignore
        ];
        $single_update_dto->stats = [
            'comments' => is_numeric($count) ? (int) $count : 0,
            'likes' => CampaignConstants::DEFAULT_LIKES_COUNT
        ];

        return $single_update_dto;
    }

    /**
     * Extract updates from query.
     *
     * @param WP_Query $query
     * @param int $page
     * @param int $limit
     * @return array
     */
    protected function extract_updates_from_query(WP_Query $query, int $page = 1, int $limit = CampaignConstants::DEFAULT_UPDATES_LIMIT)
    {
        if (!$this->is_valid_query($query)) {
            return [];
        }

        $updates = [];
        $position = 0;
        $global_position = ($page - 1) * $limit;

        while ($query->have_posts()) {
            $query->the_post();
            $post = get_post();

            if (!$this->is_valid_post($post)) {
                continue;
            }

            $final_position = $global_position + $position;
            $update_dto = $this->build_update_dto($post, $final_position);
            $updates[] = $update_dto;
            ++$position;
        }

        return $updates;
    }

    /**
     * Guard method to validate query.
     *
     * @param WP_Query $query
     * @return bool
     */
    protected function is_valid_query(WP_Query $query)
    {
        return $query instanceof WP_Query && $query->have_posts();
    }

    /**
     * Guard method to validate post.
     *
     * @param mixed $post
     * @return bool
     */
    protected function is_valid_post($post)
    {
        return $post instanceof WP_Post && $post->ID > 0;
    }

    /**
     * Build update DTO from post.
     *
     * @param WP_Post $post
     * @param int $position
     * @return CampaignUpdateDTO
     */
    protected function build_update_dto(WP_Post $post, int $position)
    {
        $update_dto = new CampaignUpdateDTO();

        $update_dto->update_id = $post->post_name;
        $update_dto->update_post_id = $post->ID;
        $update_dto->post_id = $post->ID;
        $update_dto->update_badge = 'UPDATE';
        $update_dto->update_date = $this->get_update_date($post);
        $update_dto->update_title = $post->post_title;
        $update_dto->update_image = $this->get_update_image($post->ID);
        $update_dto->update_image_alt = !empty($post->post_title) ? $post->post_title : __('Update image', 'growfund');
        $update_dto->update_content_preview = $this->get_update_content_preview($post->post_content);
        $update_dto->update_position = $position;
        $update_dto->update_creator = $this->build_update_creator($post->post_author);
        $update_dto->update_stats = $this->build_update_stats($post->ID);

        $update_dto->id = $post->post_name;
        $update_dto->badge = 'UPDATE';
        $update_dto->date = $this->get_update_date($post);
        $update_dto->title = $post->post_title;
        $update_dto->image = $this->get_update_image($post->ID);
        $update_dto->image_alt = !empty($post->post_title) ? $post->post_title : __('Update image', 'growfund');
        $update_dto->content_preview = $this->get_update_content_preview($post->post_content);
        $update_dto->content_full = $post->post_content;
        $update_dto->position = $position;
        $update_dto->creator = $this->build_update_creator($post->post_author);
        $update_dto->stats = $this->build_update_stats($post->ID);

        return $update_dto;
    }



    /**
     * Build update creator data.
     *
     * @param int $author_id
     * @return array
     */
    protected function build_update_creator(int $author_id)
    {
        $user = get_userdata($author_id);
        $display_name = UserMeta::get($author_id, 'display_name');
        $name = !empty($display_name) ? $display_name : ($user ? $user->display_name : __('Campaign Creator', 'growfund'));

        $image = UserMeta::get($author_id, 'image');

        if (!empty($image)) {
            $image = MediaAttachment::make($image);
            $image = $image['url'] ?? '';
        }

        return [
            'name' => $name,
            'avatar' => $image
        ];
    }

    /**
     * Build update stats data.
     *
     * @param int $post_id
     * @return array
     */
    protected function build_update_stats(int $post_id)
    {
        $count = get_comments($this->build_comment_query_args(
            $post_id,
            'update_comment',
            CampaignConstants::DEFAULT_PARENT_COMMENT_ID,
            'approve',
            true
        ));

        return [
            'comments' => is_numeric($count) ? (int) $count : 0,
            'likes' => CampaignConstants::DEFAULT_LIKES_COUNT
        ];
    }

    /**
     * Create empty paginated result.
     *
     * @param int $limit
     * @param int $page
     * @return PaginatedCollectionDTO
     */
    protected function create_empty_paginated_result($limit, $page)
    {
        $empty_paginated_dto = new PaginatedCollectionDTO();
        $empty_paginated_dto->results = [];
        $empty_paginated_dto->total = CampaignConstants::DEFAULT_EMPTY_TOTAL;
        $empty_paginated_dto->count = CampaignConstants::DEFAULT_EMPTY_COUNT_VALUE;
        $empty_paginated_dto->per_page = $limit ?? CampaignConstants::DEFAULT_UPDATES_LIMIT;
        $empty_paginated_dto->current_page = $page ?? CampaignConstants::DEFAULT_PAGE;
        $empty_paginated_dto->has_more = CampaignConstants::DEFAULT_HAS_MORE;
        $empty_paginated_dto->overall = 0;
        return $empty_paginated_dto;
    }

    /**
     * Create paginated result.
     *
     * @param array $results
     * @param WP_Query $query
     * @param CampaignFiltersDTO $filters_dto
     * @return PaginatedCollectionDTO
     */
    protected function create_paginated_result(array $results, WP_Query $query, CampaignFiltersDTO $filters_dto)
    {
        $paginated_dto = new PaginatedCollectionDTO();
        $paginated_dto->results = $results;
        $paginated_dto->total = $query->found_posts;
        $paginated_dto->count = count($results);
        $paginated_dto->per_page = $filters_dto->limit;
        $paginated_dto->current_page = $filters_dto->page;
        $paginated_dto->has_more = $filters_dto->page < $query->max_num_pages;
        $paginated_dto->overall = PaginationSupport::get_overall_post_count(Campaign::NAME);
        return $paginated_dto;
    }

    /**
     * Create empty updates response.
     *
     * @param PaginatedCollectionDTO $updates_data
     * @return PaginatedCollectionDTO
     */
    protected function create_empty_updates_response(PaginatedCollectionDTO $updates_data)
    {
        $empty_updates_dto = new PaginatedCollectionDTO();
        $empty_updates_dto->results = [];
        $empty_updates_dto->has_more = $updates_data->has_more;
        $empty_updates_dto->total = $updates_data->total;
        $empty_updates_dto->current_page = $updates_data->current_page;
        $empty_updates_dto->per_page = $updates_data->per_page;
        $empty_updates_dto->count = 0;
        $empty_updates_dto->overall = 0;
        return $empty_updates_dto;
    }

    /**
     * Transform updates collection.
     *
     * @param PaginatedCollectionDTO $updates_data
     * @param int $page
     * @param int $limit
     * @return array
     */
    protected function transform_updates_collection(PaginatedCollectionDTO $updates_data, int $page, int $limit)
    {
        $transformed_updates = [];

        foreach ($updates_data->results as $index => $update) {
            $update_array = is_object($update) ? (array) $update : $update;
            $transformed_updates[] = $this->transform_single_update($update_array, $index, $updates_data, $page, $limit);
        }

        return $transformed_updates;
    }

    /**
     * Transform a single update.
     *
     * @param array $update
     * @param int $index
     * @param PaginatedCollectionDTO $updates_data
     * @param int $page
     * @param int $limit
     * @return CampaignUpdateDTO
     */
    protected function transform_single_update(array $update, int $index, PaginatedCollectionDTO $updates_data, int $page, int $limit)
    {
        $position_data = $this->calculate_update_position($index, $page, $limit, $updates_data->total);

        $single_update_dto = new CampaignUpdateDTO();

        $single_update_dto->update_id = $this->get_update_id($update, $index);
        $single_update_dto->update_post_id = $update['post_id'] ?? null;
        $single_update_dto->post_id = $update['post_id'] ?? null;
        $single_update_dto->update_badge = $this->format_update_badge($position_data['update_number']);
        $single_update_dto->update_date = $this->format_update_date($update['date'] ?? '');
        $single_update_dto->update_title = $update['title'] ?? '';
        $single_update_dto->update_image = $update['thumbnail'] ?? '';
        $single_update_dto->update_image_alt = !empty($update['title'] ?? '') ? ($update['title'] ?? '') : __('Update image', 'growfund');
        $single_update_dto->update_content_preview = $update['description'] ?? '';
        $single_update_dto->update_position = $position_data['actual_position'];
        $single_update_dto->update_creator = $this->format_update_creator($update);
        $single_update_dto->update_stats = $this->format_update_stats($update);

        return $single_update_dto;
    }

    /**
     * Calculate update position and number.
     *
     * @param int $index
     * @param int $page
     * @param int $limit
     * @param int $total_posts
     * @return array
     */
    protected function calculate_update_position(int $index, int $page, int $limit, int $total_posts)
    {
        $actual_position = ($page - CampaignConstants::DEFAULT_INDEX_OFFSET) * $limit + $index;
        $update_number = $total_posts - $actual_position;

        return [
            'actual_position' => $actual_position,
            'update_number' => $update_number
        ];
    }

    /**
     * Get update ID.
     *
     * @param array $update
     * @param int $index
     * @return string
     */
    protected function get_update_id(array $update, int $index)
    {
        return $update['slug'] ?? (string) ($index + CampaignConstants::DEFAULT_INDEX_OFFSET);
    }

    /**
     * Format update badge.
     *
     * @param int $update_number
     * @return string
     */
    protected function format_update_badge(int $update_number)
    {
        return 'UPDATE #' . $update_number;
    }

    /**
     * Format update date.
     *
     * @param string $date
     * @return string
     */
    protected function format_update_date(string $date)
    {
        if (empty($date)) {
            return '';
        }

        return date(DateTimeFormats::HUMAN_READABLE_DATE, strtotime($date)); // phpcs:ignore
    }

    /**
     * Format update creator data.
     *
     * @param array $update
     * @return array
     */
    protected function format_update_creator(array $update)
    {
        return [
            'name' => $this->get_creator_name($update['author_name'] ?? ''),
            'avatar' => $update['author_image'] ?? ''
        ];
    }

    /**
     * Get creator name with fallback.
     *
     * @param string $author_name
     * @return string
     */
    protected function get_creator_name(string $author_name)
    {
        return !empty($author_name) ? $author_name : 'Campaign Creator';
    }

    /**
     * Format update stats.
     *
     * @param array $update
     * @return array
     */
    protected function format_update_stats(array $update)
    {
        return [
            'comments' => $update['comments_count'] ?? CampaignConstants::DEFAULT_COMMENTS_COUNT_FALLBACK,
            'likes' => $update['likes_count'] ?? CampaignConstants::DEFAULT_LIKES_COUNT_FALLBACK
        ];
    }

    /**
     * Create updates response.
     *
     * @param PaginatedCollectionDTO $updates_data
     * @param array $transformed_updates
     * @return PaginatedCollectionDTO
     */
    protected function create_updates_response(PaginatedCollectionDTO $updates_data, array $transformed_updates)
    {
        $updates_response_dto = new PaginatedCollectionDTO();
        $updates_response_dto->results = $transformed_updates;
        $updates_response_dto->has_more = $updates_data->has_more;
        $updates_response_dto->total = $updates_data->total;
        $updates_response_dto->current_page = $updates_data->current_page;
        $updates_response_dto->per_page = $updates_data->per_page;
        $updates_response_dto->count = count($transformed_updates);
        $updates_response_dto->overall = $updates_data->overall;
        return $updates_response_dto;
    }

    /**
     * Build common query arguments for comments.
     *
     * @param int $post_id
     * @param string $comment_type
     * @param int $parent
     * @param string $status
     * @param bool $count_only
     * @return array
     */
    protected function build_comment_query_args(
        int $post_id,
        string $comment_type = 'update_comment',
        int $parent = CampaignConstants::DEFAULT_PARENT_COMMENT_ID,
        string $status = 'approve',
        bool $count_only = false
    ) {
        $args = [
            'post_id' => $post_id,
            'status' => $status,
            'parent' => $parent,
            'comment_type' => $comment_type,
        ];

        if ($count_only) {
            $args['count'] = true;
        }

        return $args;
    }

    /**
     * Prepare archive data using services
     * @param string|null $description
     * @return CampaignTemplateDTO
     */
    public function prepare_campaigns_data($banner_title = null)
    {
        $category_service = new CampaignCategoryService();

        $query_vars = WPQueryHelper::get_query_vars();
        $query_vars['only_active'] = true;
        $query_vars['status'] = [CampaignStatus::PUBLISHED, CampaignStatus::COMPLETED, CampaignStatus::FUNDED];

        $filters_dto = CampaignFiltersDTO::from_array($query_vars);

        // Prepare data using services directly
        $data = [];
        $categories =  $category_service->get_all();
        
        $data['campaigns'] = $this->paginated($filters_dto);
        $data['categories'] = $categories;

        // Get featured campaigns
        $featured_filters = new CampaignFiltersDTO();
        $featured_filters->featured = true;
        $featured_filters->limit = 10;
        $featured_filters->only_active = true;
        $featured_filters->status = [CampaignStatus::PUBLISHED, CampaignStatus::COMPLETED, CampaignStatus::FUNDED];
        $featured_data = [];
        $featured_data['campaigns'] = $this->paginated($featured_filters);
        $featured_data['categories'] = $categories;

        $template_dto = new CampaignTemplateDTO();
        $template_dto->data = $data;
        $template_dto->featured_data = $featured_data;
        $template_dto->filter_state = $filters_dto->all();
        $template_dto->initial_limit = CampaignConstants::DEFAULT_SLIDER_LIMIT;
        $template_dto->featured_initial_limit = CampaignConstants::DEFAULT_SLIDER_LIMIT;
        $template_dto->banner_title = $banner_title;

        return $template_dto;
    }

    /**
     * Get campaign donations for the donation list component.
     *
     * @param int $campaign_id
     * @param int $limit
     * @return array
     */
    public function get_campaign_donations(int $campaign_id, int $limit = 3)
    {
        if (!growfund_app()->is_donation_mode()) {
            return [];
        }

        $donation_filter_dto = new DonationFilterParamsDTO();
        $donation_filter_dto->campaign_id = $campaign_id;
        $donation_filter_dto->limit = $limit;
        $donation_filter_dto->status = DonationStatus::COMPLETED;

        return $this->donation_service->latest($donation_filter_dto);
    }

    /**
     * Get total donation count for a campaign.
     *
     * @param int $campaign_id
     * @return int
     */
    public function get_campaign_donations_count(int $campaign_id)
    {
        if (!growfund_app()->is_donation_mode()) {
            return 0;
        }

        return $this->donation_service->get_total_count_of_donations_for_campaign($campaign_id);
    }

    /**
     * Prepare single data using services
     *
     * @return CampaignTemplateDTO
     */
    public function prepare_single_campaign_data()
    {
        $campaign_id = get_the_ID();
        $user_id = growfund_user()->get_id();

        $data = $this->prepare_campaign_details_data($campaign_id);

        $uid = growfund_input_get('uid');

        if (!empty($uid)) {
            $data->contribution = $this->get_contribution_info_by_uid(Sanitizer::apply_rule($uid, Sanitizer::TEXT)); // phpcs:ignore
        }

        $campaign_tab_service = new CampaignTabService();
        $tab_data = $campaign_tab_service->prepare_campaign_tab_data($data->id);

        $data->rewards = $tab_data->rewards;
        $data->campaign_updates = $tab_data->campaign_updates;
        $data->timeline_dates = $tab_data->timeline_dates;
        $data->total_campaign_updates_count = $tab_data->total_campaign_updates_count;
        $data->total_comments_count = $tab_data->total_comments_count;

        $data->can_see_campaign_updates = $this->can_user_see_campaign_updates_for_campaign($user_id, $data->id);

        if (growfund_app()->is_donation_mode()) {
            $data->can_show_donations = $this->can_user_see_campaign_contributions();

            if ($data->can_show_donations) {
                $data->campaign_donations = $this->get_campaign_donations($data->id);
                $data->campaign_donations_count = $this->get_campaign_donations_count($data->id);
            }
        }

        $data->checkout_url = Utils::get_checkout_url($data->id);

        $current_user_id = growfund_user()->get_id();

        if ($current_user_id > 0) {
            $data->is_bookmarked = $this->bookmark_service->is_bookmarked($current_user_id, $data->id);
        } else {
            $data->is_bookmarked = false;
        }

        $template_dto = new CampaignTemplateDTO();
        $template_dto->data = $data;
        $template_dto->campaign_id = $data->id;

        return $template_dto;
    }

    protected function get_contribution_info_by_uid($uid)
    {
        try {
            $confirmation_dto = null;

            if (growfund_app()->is_donation_mode()) {
                $donation_dto = $this->donation_service->get_by_uid($uid);
                $donation_dto->get_values();

                $confirmation_dto = new OrderConfirmationDTO();
                $confirmation_dto->campaign_title = $donation_dto->campaign->title;
                $confirmation_dto->fund_title = $donation_dto->fund->title;
                $confirmation_dto->ref_number = $donation_dto->id;
                $confirmation_dto->contributor_name = $donation_dto->donor->first_name . ' ' . $donation_dto->donor->last_name;
                $confirmation_dto->contributor_email = $donation_dto->donor->email;
                $confirmation_dto->amount = $donation_dto->amount;
                $confirmation_dto->payment_method = $donation_dto->payment_method->label ?? '___';
                $confirmation_dto->confirmation_title = PostMeta::get($donation_dto->campaign->id, 'confirmation_title');
                $confirmation_dto->confirmation_description = PostMeta::get($donation_dto->campaign->id, 'confirmation_description');

                return $confirmation_dto;
            }

            $pledge_dto = $this->pledge_service->get_by_uid($uid);
            $pledge_dto->payment->get_values();

            $confirmation_dto = new OrderConfirmationDTO();
            $confirmation_dto->campaign_title = $pledge_dto->campaign->title;
            $confirmation_dto->reward_title = $pledge_dto->reward->title ?? '';
            $confirmation_dto->ref_number = $pledge_dto->id;
            $confirmation_dto->contributor_name = $pledge_dto->backer->first_name . ' ' . $pledge_dto->backer->last_name;
            $confirmation_dto->contributor_email = $pledge_dto->backer->email;
            $confirmation_dto->amount = $pledge_dto->payment->total;
            $confirmation_dto->payment_method = $pledge_dto->payment->payment_method->label ?? '___';
            $confirmation_dto->confirmation_title = PostMeta::get($pledge_dto->campaign->id, 'confirmation_title');
            $confirmation_dto->confirmation_description = PostMeta::get($pledge_dto->campaign->id, 'confirmation_description');
            $confirmation_dto->is_future_payment = growfund_support_future_payment($pledge_dto->payment->payment_method->name) && in_array($pledge_dto->status, [PledgeStatus::PENDING, PledgeStatus::IN_PROGRESS, PledgeStatus::BACKED], true);

            return $confirmation_dto;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Check if user can see campaign donations/contributions
     * 
     * Evaluates user access to view campaign contribution lists based on:
     * - Global contributor list visibility setting
     * - User authentication status
     *
     * @return bool True if user can see contributions, false otherwise
     */
    protected function can_user_see_campaign_contributions()
    {
        $setting_value = $this->campaign_settings->display_contributor_list_publicly();
        $is_logged_in = growfund_user()->is_logged_in();

        return $setting_value || $is_logged_in;
    }

    /**
     * Check if campaign updates should be visible to the current user
     * 
     * Evaluates user access to campaign updates based on:
     * - Global visibility settings (public, logged-in users, contributors only)
     * - User authentication status
     * - User's contribution status to the specific campaign (if applicable)
     *
     * @param mixed $user_id
     * @param int|null $campaign_id Optional campaign ID to check specific campaign access
     * @return bool True if user can see updates, false otherwise
     */
    public function can_user_see_campaign_updates_for_campaign($user_id, $campaign_id = null)
    {
        $visibility = $this->campaign_settings->campaign_update_visibility();

        if ($visibility === Visibility::PUBLIC) {
            return true;
        }

        if ($visibility === Visibility::LOGGED_IN_USERS) {
            return growfund_user()->is_logged_in();
        }

        if ($visibility === Visibility::CONTRIBUTORS) {
            if (!growfund_user()->is_logged_in()) {
                return false;
            }

            if ($campaign_id) {
                return $this->has_contribution($campaign_id, $user_id);
            }
        }

        return true;
    }

    /**
     * Check if the current user has backed a specific campaign
     * 
     * Verifies user contribution status by checking either donations (donation mode)
     * or pledges (growfund mode) with completed payment status.
     *
     * @param int $campaign_id The campaign ID to check
     * @return bool True if user has backed the campaign, false otherwise
     */
    public function has_contribution($campaign_id, $user_id)
    {
        if (growfund_app()->is_donation_mode()) {
            return $this->donation_service->has_contributions($campaign_id, $user_id) > 0;
        }

        return $this->pledge_service->has_contributions($campaign_id, $user_id) > 0;
    }
}
