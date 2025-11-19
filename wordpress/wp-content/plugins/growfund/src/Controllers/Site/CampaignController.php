<?php

namespace Growfund\Controllers\Site;

defined( 'ABSPATH' ) || exit;

use Growfund\Contracts\Request;
use Growfund\DTO\Site\Campaign\CampaignFiltersDTO;
use Growfund\DTO\Site\AjaxResponseDTO;
use Growfund\Services\CampaignCategoryService;
use Growfund\Services\Site\RewardService;
use Growfund\Services\Site\CampaignService;
use Growfund\Services\Site\CampaignTabService;
use Growfund\Services\BookmarkService;
use Growfund\Constants\Site\Campaign as CampaignConstants;
use Growfund\Constants\Status\CampaignStatus;
use Growfund\Constants\Status\DonationStatus;
use Growfund\DTO\Donation\DonationFilterParamsDTO;
use Growfund\PostTypes\Campaign;
use Growfund\Services\DonationService;
use Growfund\Supports\Arr;
use Growfund\Validation\Validator;

class CampaignController
{
    protected $campaign_service;
    protected $category_service;
    protected $reward_service;
    protected $campaign_tab_service;
    protected $access_control_service;
    protected $bookmark_service;

    public function __construct()
    {
        $this->campaign_service = new CampaignService();
        $this->category_service = new CampaignCategoryService();
        $this->reward_service = new RewardService();
        $this->campaign_tab_service = new CampaignTabService();
        $this->bookmark_service = new BookmarkService();
    }

    /**
     * Handle ajax request for campaigns archive.
     *
     * @param Request $request
     * @return \Growfund\Http\Response
     */
    public function ajax_get_campaigns_archive(Request $request)
    {
        $filters = CampaignFiltersDTO::from_array($request->all());
        $filters->status = [CampaignStatus::PUBLISHED, CampaignStatus::COMPLETED, CampaignStatus::FUNDED];
        $filters->only_active = true;
        $is_ajax_load = $request->get_bool('is_ajax_load', false);

        $campaigns_data = $this->campaign_service->paginated($filters);

        $html = growfund_renderer()->get_html('site.components.campaign-list', [
            'campaigns' => $campaigns_data,
            'is_ajax_load' => $is_ajax_load,
        ]);

        $response_dto = new AjaxResponseDTO([
            'html' => $html,
            'has_more' => $campaigns_data->has_more,
            'additional_data' => [
                'is_ajax_load' => $is_ajax_load,
            ],
        ]);

        return growfund_site_response()->json($response_dto);
    }

    /**
     * Handle ajax request for searching campaigns.
     *
     * @param Request $request
     * @return \Growfund\Http\Response
     */
    public function ajax_search_campaigns(Request $request)
    {
        $filters = CampaignFiltersDTO::from_array($request->all());
        $filters->status = [CampaignStatus::PUBLISHED, CampaignStatus::COMPLETED, CampaignStatus::FUNDED];
        $filters->only_active = true;
        $is_ajax_load = $request->get_bool('is_ajax_load', false);

        $campaigns_data = $this->campaign_service->paginated($filters);

        $html = growfund_renderer()->get_html('site.components.campaign-list', [
            'campaigns' => $campaigns_data,
            'is_ajax_load' => $is_ajax_load,
        ]);

        $response_dto = new AjaxResponseDTO([
            'html' => $html,
            'has_more' => $campaigns_data->has_more,
            'total' => $campaigns_data->total,
            'additional_data' => [
                'is_ajax_load' => $is_ajax_load,
            ],
        ]);

        return growfund_site_response()->json($response_dto);
    }

    /**
     * Handle ajax request for campaign slider items.
     *
     * @param Request $request
     * @return void
     */
    public function ajax_get_campaign_slider_items(Request $request)
    {
        $variant = $request->get_string('variant', 'list');
        $requested_limit = $request->get_int('limit', CampaignConstants::DEFAULT_SLIDER_LIMIT);

        $filters_data = [
            'page' => $request->get_int('page', CampaignConstants::DEFAULT_PAGE),
            'limit' => $requested_limit,
        ];

        if ($variant === 'featured') {
            $filters_data['featured'] = true;
        }

        $filters = CampaignFiltersDTO::from_array($filters_data);
        $filters->status = [CampaignStatus::PUBLISHED, CampaignStatus::COMPLETED, CampaignStatus::FUNDED];
        $filters->only_active = true;
        $campaigns_data = $this->campaign_service->paginated($filters);

        if (!empty($campaigns_data->results)) {
            $campaignItems = Arr::make($campaigns_data->results)
                ->map(function ($campaign) use ($variant) {
                    return [
                        'campaign' => $campaign,
                        'variant' => $variant,
                    ];
                })
                ->toArray();

            $html = growfund_renderer()->get_html('site.components.campaign-slider-item', $campaignItems);

            $response_dto = new AjaxResponseDTO([
                'html' => $html,
                'has_more' => $campaigns_data->has_more,
                'total' => $campaigns_data->total,
            ]);

            return growfund_site_response()->json($response_dto);
        }
    }

    /**
     * Handle ajax request for campaign updates.
     *
     * @param Request $request
     * @return void
     */
    public function ajax_get_campaign_updates(Request $request)
    {
        $campaign_id = $request->get_int('campaign_id');
        $page = $request->get_int('page', CampaignConstants::DEFAULT_PAGE);
        $limit = $request->get_int('limit', CampaignConstants::DEFAULT_UPDATES_LIMIT);

        if (!$campaign_id) {
            return growfund_site_response()->json_error(['message' => __('Campaign ID is required', 'growfund')]);
        }

        $updates_data = $this->campaign_service->get_campaign_updates_paginated($campaign_id, $page, $limit);

        $html = '';
        if (!empty($updates_data->results)) {
            foreach ($updates_data->results as $update) {
                $html .= growfund_renderer()->get_html('site.components.update-item', ['data' => $update]);
            }
        }

        $response_dto = new AjaxResponseDTO([
            'html' => $html,
            'has_more' => $updates_data->has_more ?? false,
            'total' => $updates_data->total ?? 0,
            'current_page' => $updates_data->current_page ?? $page,
            'per_page' => $updates_data->per_page ?? $limit,
            'count' => $updates_data->count ?? 0,
        ]);

        return growfund_site_response()->json($response_dto);
    }

    /**
     * Handle ajax request for campaign rewards.
     *
     * @param Request $request
     * @return void
     */
    public function ajax_get_campaign_rewards(Request $request)
    {
        $campaign_id = $request->get_int('campaign_id');
        $page = $request->get_int('page', CampaignConstants::DEFAULT_PAGE);
        $limit = $request->get_int('limit', CampaignConstants::DEFAULT_REWARDS_LIMIT);

        if (!$campaign_id) {
            return growfund_site_response()->json_error(['message' => __('Campaign ID is required', 'growfund')]);
        }

        $frontend_rewards_data = $this->reward_service->paginated($campaign_id, $page, $limit);

        if (!empty($frontend_rewards_data->results)) {
            $html = growfund_renderer()->get_html('site.components.campaign-reward', ['rewards' => $frontend_rewards_data->results]);

            $response_dto = new AjaxResponseDTO([
                'html' => $html,
                'has_more' => $frontend_rewards_data->has_more ?? false,
                'total_pages' => ceil($frontend_rewards_data->total / $limit),
                'current_page' => $frontend_rewards_data->current_page ?? $page,
                'total_posts' => $frontend_rewards_data->total ?? 0,
            ]);

            return growfund_site_response()->json($response_dto);
        }
    }

    /**
     * Handle ajax request for a single update's detail view.
     *
     * @param Request $request
     * @return void
     */
    public function ajax_get_update_detail(Request $request)
    {
        $campaign_id = $request->get_int('campaign_id');
        $campaign_update_id = $request->get_string('campaign_update_id');

        if (!$campaign_id || !$campaign_update_id) {
            return growfund_site_response()->json_error(['message' => __('Campaign ID and Campaign Update ID are required', 'growfund')]);
        }

        $update_data = $this->campaign_service->get_single_update_data($campaign_id, $campaign_update_id);

        if (!$update_data) {
            return growfund_site_response()->json_error(['message' => __('Update not found', 'growfund')]);
        }

        $html = growfund_renderer()->get_html('site.components.update-detail-view', ['data' => $update_data]);

        $response_dto = new AjaxResponseDTO([
            'html' => $html,
            'additional_data' => [
                'campaign_update_id' => $campaign_update_id,
            ],
        ]);

        return growfund_site_response()->json($response_dto);
    }



    /**
     * Handle ajax request for campaign donations modal with pagination and sorting.
     *
     * @param Request $request
     * @return \Growfund\Http\Response
     */
    public function ajax_get_campaign_donations_modal(Request $request)
    {
        $campaign_id = $request->get_int('campaign_id');
        $page = $request->get_int('page', 1);
        $limit = $request->get_int('limit', 10);
        $sort = $request->get_string('sort', CampaignConstants::SORT_NEWEST);

        if (!$campaign_id) {
            return growfund_site_response()->json_error(['message' => __('Campaign ID is required', 'growfund')]);
        }

        if (!growfund_app()->is_donation_mode()) {
            return growfund_site_response()->json_error(['message' => __('Donation mode is not enabled', 'growfund')]);
        }

        $donation_filter_dto = new DonationFilterParamsDTO();
        $donation_filter_dto->campaign_id = $campaign_id;
        $donation_filter_dto->page = $page;
        $donation_filter_dto->limit = $limit;
        $donation_filter_dto->status = DonationStatus::COMPLETED;

        if ($sort === CampaignConstants::SORT_TOP) {
            $donation_filter_dto->orderby = 'amount';
            $donation_filter_dto->order = 'DESC';
        } else {
            $donation_filter_dto->orderby = 'created_at';
            $donation_filter_dto->order = 'DESC';
        }

        $donation_service = new DonationService();
        $donations_data = $donation_service->paginated($donation_filter_dto);

        $html = growfund_renderer()->get_html('site.components.donation-modal-list', [
            'donations' => $donations_data->results ?? [],
            'pagination' => $donations_data,
            'sort' => $sort
        ]);

        $response_dto = new AjaxResponseDTO([
            'html' => $html,
            'has_more' => $donations_data->has_more ?? false,
            'total' => $donations_data->total ?? 0,
            'current_page' => $donations_data->current_page ?? $page,
            'per_page' => $donations_data->per_page ?? $limit,
            'count' => count($donations_data->results ?? []),
            'additional_data' => [
                'campaign_id' => $campaign_id,
                'sort' => $sort,
            ],
        ]);

        return growfund_site_response()->json($response_dto);
    }

    /**
     * Handle ajax request for bookmarking a campaign.
     *
     * @param Request $request
     * @return \Growfund\Http\Response
     */
    public function ajax_bookmark_campaign(Request $request)
    {
        $campaign_id = $request->get_int('campaign_id');
        $user_id = growfund_user()->get_id();

        $validator = Validator::make($request->all(), [
            'campaign_id' => 'required|integer|post_exists:post_type=' . Campaign::NAME
        ]);

        if ($validator->is_failed()) {
            return growfund_site_response()->json_error($validator->get_errors());
        }

        $is_bookmarked = $this->bookmark_service->is_bookmarked($user_id, $campaign_id);

        if ($is_bookmarked) {
            $this->bookmark_service->remove_bookmark($user_id, $campaign_id);
            $is_bookmarked = false;
        } else {
            $this->bookmark_service->create_bookmark($user_id, $campaign_id);
            $is_bookmarked = true;
        }

        $response_dto = new AjaxResponseDTO([
            'additional_data' => [
                'campaign_id' => $campaign_id,
                'user_id' => $user_id,
                'is_bookmarked' => $is_bookmarked
            ],
        ]);

        return growfund_site_response()->json($response_dto);
    }
}
