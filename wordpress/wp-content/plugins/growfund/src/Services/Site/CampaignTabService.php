<?php

namespace Growfund\Services\Site;

defined( 'ABSPATH' ) || exit;

use Growfund\DTO\Site\Campaign\CampaignTabDataDTO;
use Growfund\DTO\Site\Campaign\TimelineDatesDTO;
use Growfund\DTO\Site\Campaign\UpdateNavigationDTO;
use Growfund\DTO\Site\Campaign\CampaignUpdateDetailsDTO;
use Growfund\DTO\PaginatedCollectionDTO;
use Growfund\DTO\Site\Comment\CommentFormDTO;
use Growfund\DTO\Site\Comment\CommentDTO;
use Growfund\Services\Site\RewardService;
use Growfund\Services\Site\CommentService;
use Growfund\Constants\Site\Reward as RewardConstants;
use Growfund\Constants\Site\Comment as CommentConstants;
use Growfund\Constants\Site\Campaign as CampaignConstants;
use Growfund\Constants\DateTimeFormats;
use Growfund\Core\AppSettings;
use Growfund\Supports\PostMeta;

/**
 * Service for handling campaign tab data processing
 * 
 * This service follows strict DTO usage patterns:
 * - All data transformations use proper DTOs
 * - Type safety is maintained throughout
 * - Clear separation between DTOs and arrays
 * - Consistent naming conventions
 */
class CampaignTabService
{
    protected $reward_service;
    protected $comment_service;
    protected $campaign_service;

    public function __construct()
    {
        $this->reward_service = new RewardService();
        $this->comment_service = new CommentService();
        $this->campaign_service = new CampaignService();
    }

    /**
     * Prepare campaign tab data for frontend display
     *
     * @param int $campaign_id
     * @return CampaignTabDataDTO
     */
    public function prepare_campaign_tab_data(int $campaign_id)
    {
        $rewards_data = $this->reward_service->paginated(
            $campaign_id,
            CampaignConstants::DEFAULT_PAGE,
            CampaignConstants::DEFAULT_REWARDS_LIMIT
        );
        $timeline_dates_dto = $this->prepare_timeline_dates($campaign_id);
        $campaign_updates_data = $this->prepare_campaign_updates($campaign_id);
        $total_campaign_updates_count = $this->campaign_service->get_campaign_updates_count($campaign_id);
        $total_comments_count = $this->comment_service->get_comments_count($campaign_id, 'comment');

        $campaign_tab_dto = new CampaignTabDataDTO();
        $campaign_tab_dto->campaign_id = $campaign_id;
        $campaign_tab_dto->rewards = $rewards_data->results ?? [];
        $campaign_tab_dto->campaign_updates = $campaign_updates_data->results;
        $campaign_tab_dto->timeline_dates = $timeline_dates_dto;
        $campaign_tab_dto->total_campaign_updates_count = $total_campaign_updates_count;
        $campaign_tab_dto->total_comments_count = $total_comments_count;

        return $campaign_tab_dto;
    }

    /**
     * Prepare timeline dates for campaign
     *
     * @param int $campaign_id
     * @return TimelineDatesDTO
     */
    public function prepare_timeline_dates(int $campaign_id)
    {
        $start_date = PostMeta::get($campaign_id, 'start_date');
        $end_date = PostMeta::get($campaign_id, 'end_date');

        $start_timestamp = !empty($start_date) ? strtotime($start_date) : time();
        $end_timestamp = !empty($end_date) ? strtotime($end_date) : time();

        return TimelineDatesDTO::from_array([
            'start_date' => !empty($start_date) ? $start_date : wp_date(DateTimeFormats::DB_DATE, $start_timestamp),
            'end_date' => !empty($end_date) ? $end_date : wp_date(DateTimeFormats::DB_DATE, $end_timestamp),
            'start_date_formatted' => wp_date(RewardConstants::DATE_FORMAT_SHORT, $start_timestamp),
            'end_date_formatted' => wp_date(RewardConstants::DATE_FORMAT_SHORT, $end_timestamp),
            'launch_date_formatted' => wp_date(RewardConstants::DATE_FORMAT_LONG, $start_timestamp)
        ]);
    }

    /**
     * Prepare campaign updates with comments and navigation
     *
     * @param int $campaign_id
     * @return PaginatedCollectionDTO Collection of prepared update DTOs
     */
    public function prepare_campaign_updates(int $campaign_id)
    {
        $updates_result = $this->campaign_service->get_campaign_updates_paginated(
            $campaign_id,
            CampaignConstants::DEFAULT_PAGE,
            CampaignConstants::DEFAULT_UPDATES_LIMIT
        );

        $updates = $updates_result->results ?? [];
        $prepared_updates = [];

        foreach ($updates as $index => $update) {
            $update_details_dto = $this->prepare_single_update_with_comments($update, $index, $updates);
            $prepared_updates[] = $update_details_dto;
        }

        $collection_dto = new PaginatedCollectionDTO();
        $collection_dto->results = $prepared_updates;
        $collection_dto->has_more = $updates_result->has_more;
        $collection_dto->total = $updates_result->total;
        $collection_dto->current_page = $updates_result->current_page;
        $collection_dto->per_page = $updates_result->per_page;
        $collection_dto->count = count($prepared_updates);
        $collection_dto->overall = $updates_result->overall;

        return $collection_dto;
    }

    /**
     * Prepare single update with comments and navigation
     *
     * @param mixed $current_update
     * @param int $currentIndex
     * @param array $allCampaignUpdates
     * @return CampaignUpdateDetailsDTO
     */
    public function prepare_single_update_with_comments($current_update, int $currentIndex, array $allCampaignUpdates)
    {
        $post_id = $current_update->post_id ?? $current_update->update_post_id ?? null;
        $comments_data = [];
        $comment_form_data = [];

        if ($post_id) {
            $query_dto = new CommentDTO([
                'post_id' => $post_id,
                'page' => RewardConstants::DEFAULT_PAGE,
                'per_page' => RewardConstants::DEFAULT_COMMENTS_PER_PAGE,
                'comment_type' => CommentConstants::TYPE_UPDATE_COMMENT
            ]);
            $comments_result = $this->comment_service->get_comments($query_dto);
            $comments_data = $comments_result->comments ?? [];

            if (growfund_settings(AppSettings::CAMPAIGNS)->allow_comments()) {
                $comment_form_dto = new CommentFormDTO([
                    'comment_post_id' => $post_id,
                    'comment_type' => CommentConstants::TYPE_UPDATE_COMMENT,
                    'placeholder' => __('Share your thoughts, ask questions or show your support...', 'growfund'),
                    'button_text' => __('Post Comment', 'growfund')
                ]);

                $comment_form_data = $this->comment_service->prepare_comment_form_data($comment_form_dto);

                if ($comment_form_data === null) {
                    $comment_form_data = [];
                }
            } else {
                $comment_form_data = [];
            }
        }

        $navigation_dto = $this->prepare_update_navigation($allCampaignUpdates, $currentIndex);

        $update_details_dto = new CampaignUpdateDetailsDTO();

        // Core update properties
        $update_details_dto->id = $current_update->id ?? $current_update->update_id ?? 0;
        $update_details_dto->post_id = $current_update->post_id ?? $current_update->update_post_id ?? 0;
        $update_details_dto->title = $current_update->title ?? $current_update->update_title ?? '';
        $update_details_dto->description = $current_update->content_preview ?? $current_update->update_content_preview ?? '';
        $update_details_dto->slug = $current_update->slug ?? '';
        $update_details_dto->thumbnail = $current_update->image ?? $current_update->update_image ?? '';
        $raw_date = $current_update->date ?? $current_update->update_date ?? '';
        $update_details_dto->date = !empty($raw_date) ? wp_date(DateTimeFormats::HUMAN_READABLE_DATE, strtotime($raw_date)) : '';

        // Author data
        $creator = $current_update->creator ?? $current_update->update_creator ?? [];
        $update_details_dto->author_name = $creator['name'] ?? '';
        $update_details_dto->author_image = $creator['avatar'] ?? '';

        // Stats data
        $stats = $current_update->stats ?? $current_update->update_stats ?? [];
        $update_details_dto->comments_count = $stats['comments'] ?? 0;
        $update_details_dto->likes_count = $stats['likes'] ?? 0;
        $update_details_dto->total_comments_count = count($comments_data);

        // Comments and form data
        $update_details_dto->comments = $comments_data;
        $update_details_dto->comment_form_data = $comment_form_data;
        $update_details_dto->navigation = $navigation_dto;

        // Update-specific properties for template compatibility
        $update_details_dto->update_id = $current_update->update_id ?? $current_update->id ?? null;
        $update_details_dto->update_title = $current_update->update_title ?? $current_update->title ?? '';
        $update_details_dto->update_date = !empty($raw_date) ? wp_date(DateTimeFormats::HUMAN_READABLE_DATE, strtotime($raw_date)) : '';
        $update_details_dto->update_image = $current_update->update_image ?? $current_update->image ?? '';
        $update_details_dto->update_image_alt = $current_update->update_image_alt ?? $current_update->image_alt ?? '';
        $update_details_dto->update_content_preview = $current_update->update_content_preview ?? $current_update->content_preview ?? '';
        $update_details_dto->update_content_full = $current_update->content_full ?? '';
        $update_details_dto->update_position = $current_update->update_position ?? $current_update->position ?? 0;
        $update_details_dto->update_creator = $current_update->update_creator ?? $current_update->creator ?? [];
        $update_details_dto->update_stats = $current_update->update_stats ?? $current_update->stats ?? [];
        $update_details_dto->update_badge = $current_update->update_badge ?? $current_update->badge ?? '';

        return $update_details_dto;
    }

    /**
     * Prepare update navigation data
     *
     * @param array $updates
     * @param int $current_index
     * @return UpdateNavigationDTO
     */
    public function prepare_update_navigation(array $updates, int $current_index)
    {
        $prev_update = ($current_index > RewardConstants::FIRST_INDEX)
            ? $updates[$current_index - RewardConstants::INDEX_OFFSET]
            : null;
        $has_next_update = ($current_index < count($updates) - RewardConstants::INDEX_OFFSET);
        $next_update = $has_next_update
            ? $updates[$current_index + RewardConstants::INDEX_OFFSET]
            : null;

        $prev_id = $this->extract_update_id($prev_update);
        $next_id = $this->extract_update_id($next_update);

        return UpdateNavigationDTO::from_array([
            'prev_id' => $prev_id,
            'next_id' => $next_id
        ]);
    }

    /**
     * Extract update ID from update object or array
     *
     * @param mixed $update
     * @return int|null
     */
    protected function extract_update_id($update)
    {
        if (!$update) {
            return null;
        }

        $id = null;

        if (is_object($update)) {
            // Handle CampaignUpdateDTO objects specifically
            if (property_exists($update, 'update_post_id') && $update->update_post_id) {
                $id = $update->update_post_id;
            } elseif (property_exists($update, 'post_id') && $update->post_id) {
                $id = $update->post_id;
            } else {
                $id = $update->update_id ?? $update->id ?? null;
            }
        } elseif (is_array($update)) {
            $id = $update['update_post_id'] ?? $update['post_id'] ?? $update['update_id'] ?? $update['id'] ?? null;
        }

        // Convert to int if it's a string or ensure it's an integer
        if ($id !== null) {
            $id = (int) $id;
            return $id > 0 ? $id : null;
        }

        return null;
    }
}
