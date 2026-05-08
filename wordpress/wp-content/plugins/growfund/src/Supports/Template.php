<?php

namespace Growfund\Supports;

defined( 'ABSPATH' ) || exit;

use Exception;
use Growfund\Capabilities\CampaignCapabilities;
use Growfund\Constants\Status\CampaignStatus;
use Growfund\Constants\UserTypes\Collaborator;
use Growfund\Core\AppSettings;
use Growfund\Core\AssetHandler;
use Growfund\DTO\Campaign\CampaignFiltersDTO;
use Growfund\Sanitizer;
use Growfund\Services\CampaignCategoryService;
use Growfund\Services\CampaignService;
use Growfund\Services\DonationService;
use Growfund\Services\PledgeService;
use Growfund\Services\RewardService;
use Growfund\Views\Pages\CampaignArchivePage;
use Growfund\Views\Pages\CampaignSinglePage;

class Template
{
    /**
     * Get campaign archive content.
     * 
     * @return string - html
     */
    public static function get_campaign_archive_content($banner_title = null)
    {
        $allowed_order_columns = ['created_date', 'start_date', 'end_date'];
        $orderby = growfund_input_get('orderby');
        $orderby = in_array($orderby, $allowed_order_columns, true) ? $orderby : null;

        $filters_dto = new CampaignFiltersDTO();
        $filters_dto->page = 1;
        $filters_dto->limit = 12;
        $filters_dto->search = growfund_input_get('search');
        $filters_dto->orderby = Sanitizer::apply_rule($orderby, Sanitizer::COLUMN);
        $filters_dto->order = growfund_input_get('order');
        $filters_dto->category_slug = growfund_input_get('category_slug');
        $filters_dto->status = 'launched-and-beyond';

        $featured_filters_dto = new CampaignFiltersDTO();
        $featured_filters_dto->page = 1;
        $featured_filters_dto->limit = 6;
        $featured_filters_dto->is_featured = 1;
        $featured_filters_dto->status = 'launched-and-beyond';

		$campaign_service = new CampaignService(); 
        $category_service = new CampaignCategoryService();       

        $campaign_archive_page = new CampaignArchivePage();
        $campaign_archive_page->banner_title = $banner_title;
        $campaign_archive_page->featured_campaigns = $campaign_service->paginated($featured_filters_dto);
        $campaign_archive_page->campaigns = $campaign_service->paginated($filters_dto);
        $campaign_archive_page->categories = $category_service->get_top_level_categories();
        $campaign_archive_page->search = $filters_dto->search;
        $campaign_archive_page->orderby = $filters_dto->orderby;
        $campaign_archive_page->order = $filters_dto->order;
        $campaign_archive_page->category_slug = $filters_dto->category_slug;

        $html = growfund_get_html($campaign_archive_page);

        growfund_app(AssetHandler::class)->load_assets();

        return $html;
    }

    /**
     * Get campaign details content.
     * 
     * @return string - html
     */
    public static function get_campaign_details_content()
    {
        $campaign_id = (int) get_the_ID();

        if (growfund_settings(AppSettings::CAMPAIGNS)->is_login_required_to_view_campaign_detail() && !growfund_user()->is_logged_in()) {
            return growfund_redirect(growfund_login_url(growfund_campaign_url($campaign_id)));
        }

        $campaign_status = PostMeta::get($campaign_id, 'status');

        if (
            !in_array($campaign_status, [CampaignStatus::COMPLETED, CampaignStatus::FUNDED, CampaignStatus::PUBLISHED], true) 
            && !growfund_user()->can(CampaignCapabilities::READ, $campaign_id)
        ) {
            return growfund_redirect(home_url());
        }

        $campaign_service = new CampaignService();
        $campaign = $campaign_service->get_by_id($campaign_id);

        $campaign_single_page = new CampaignSinglePage();
        $campaign_single_page->campaign = $campaign;

        $campaign_single_page->fundraiser = $campaign->fundraiser;
        $campaign_single_page->collaborators = $campaign->show_collaborator_list 
            ? $campaign_service->get_collaborator_list_by_id($campaign->id) 
            : [];

        $campaign_single_page->rewards = (new RewardService())->get_all($campaign_id);

        if ($campaign->category) {
            try {
                $category = (new CampaignCategoryService())->get_by_id($campaign->category);
				$campaign_single_page->recommended_campaigns = $campaign_service->get_recommended_campaigns($category->slug);
            } catch (Exception $e) {
                $campaign_single_page->recommended_campaigns = [];
            }
        }

        $contribution_confirmed = growfund_flash_get_message('contribution_confirmed');

        if (!empty($contribution_confirmed) && !empty($contribution_confirmed['uid'])) {
            $campaign_single_page->has_toaster = true;

            if (growfund_app()->is_donation_mode()) {
                $campaign_single_page->donation = (new DonationService())->get_by_uid($contribution_confirmed['uid']);
            } else {
                $campaign_single_page->pledge = (new PledgeService())->get_by_uid($contribution_confirmed['uid']);
            }
        }

        $contribution_failed = growfund_flash_get_message('contribution_failed');

        if (!empty($contribution_failed) && !empty($contribution_failed['is_failed'])) {
            $campaign_single_page->has_toaster = true;
        }

        $html = growfund_get_html($campaign_single_page);

        growfund_app(AssetHandler::class)->load_assets();

        return $html;
    }
}
