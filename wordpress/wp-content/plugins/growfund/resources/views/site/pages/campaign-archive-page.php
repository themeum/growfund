<?php
/**
 * @var Growfund\Views\Pages\CampaignArchivePage $campaign_archive_page
 */

defined( 'ABSPATH' ) || exit;

use Growfund\Views\Components\Campaign\CampaignFilter;
use Growfund\Views\Components\Campaign\CampaignList;
use Growfund\Views\Components\Campaign\CampaignSlider;
use Growfund\Views\Components\UI\InfiniteScroll;

?>

<div class="growfund-container">
    <div class="growfund-campaign-archive-page">
        <?php if ($campaign_archive_page->banner_title ) : ?>
        <div class="growfund-archive-page-banner">
            <div class="growfund-archive-page-banner-content">
                <h1 class="growfund-archive-page-banner-title"><?php growfund_echo_safe_html($campaign_archive_page->banner_title); ?></h1>
            </div>
        </div>
        <?php endif; ?>
        <div class="growfund-campaign-archive-page-content">
            <?php
            $growfund_campaign_filter = new CampaignFilter();
            $growfund_campaign_filter->categories = $campaign_archive_page->categories ?? [];
            $growfund_campaign_filter->search = $campaign_archive_page->search;
            $growfund_campaign_filter->category_slug = $campaign_archive_page->category_slug;
            $growfund_campaign_filter->orderby = $campaign_archive_page->orderby;
            $growfund_campaign_filter->order = $campaign_archive_page->order;

            growfund_render($growfund_campaign_filter);            

            $growfund_slider = new CampaignSlider();
            $growfund_slider->label = __('Featured Campaigns', 'growfund');
            $growfund_slider->campaigns = $campaign_archive_page->featured_campaigns->results ?? [];
            $growfund_slider->id = 'growfund_featured_campaigns_slider';

            $growfund_is_searching = !empty($campaign_archive_page->search) || !empty($campaign_archive_page->category_slug) || !empty($campaign_archive_page->orderby);

			if ($growfund_is_searching) {
				$growfund_slider->classname = 'growfund-hidden';
			}

			if (!empty($growfund_slider->campaigns)) {
				growfund_render($growfund_slider);
			}

			?>

            <div class="growfund-campaign-archive-page-campaigns-list-section">
                <h4 class="growfund-campaign-archive-page-campaigns-listing-header"><?php echo esc_html__('All Campaigns', 'growfund'); ?></h4>
                <?php 
                    $growfund_campaign_list = new CampaignList();
                    $growfund_campaign_list->campaigns = $campaign_archive_page->campaigns->results ?? [];
                    $growfund_campaign_list->id = 'growfund_archive_page_campaigns_list';
                    $growfund_campaign_list->classname = 'growfund-archive-page-campaign-list';

                    growfund_render($growfund_campaign_list);
                ?>
            </div>
            <?php 
                $growfund_infinite_scroll = new InfiniteScroll();
                $growfund_infinite_scroll->id = 'growfund_archive_page_campaigns_infinite_scroll';

                growfund_render($growfund_infinite_scroll);
            ?>
        </div>
    </div>
</div>

