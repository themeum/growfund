<?php
/**
 * @var Growfund\Views\Components\CampaignUpdate\UpdateContainer $update_container
 */

use Growfund\Views\Components\CampaignUpdate\EmptyUpdate;
use Growfund\Views\Components\CampaignUpdate\UpdateList;

defined('ABSPATH') || exit;

?>


<div class="growfund-update-tab-content-card-container">
    <div class="growfund-update-empty-state growfund-hidden">
        <p><?php esc_html_e('There are no updates available for this campaign.', 'growfund'); ?></p>
    </div>
    <div class="growfund-update-tab-content-card-available-updates-container">
    <div class="growfund-update-tab-content-card-timeline-container">
        <div 
            class="growfund-update-tab-content-card-wrapper" 
            data-campaign-id="<?php echo esc_attr($update_container->campaign_id); ?>"
            data-current-page="0"
        >
            <?php 
           
                $growfund_campaign_update_list = new UpdateList();
                $growfund_campaign_update_list->updates = $update_container->updates;
                $growfund_campaign_update_list->campaign_id = $update_container->campaign_id;
                growfund_render($growfund_campaign_update_list);
            ?>
        </div>
        <div class="growfund-update-tab-content-wrapper">
            <div class="growfund-update-tab-content-timeline growfund-hidden">
                <div class="growfund-update-tab-content-timeline-start">
                        <span class="growfund-update-tab-content-timeline-date"></span>
                </div>
                <div class="growfund-update-tab-content-timeline-line-container">
                    <div class="growfund-update-tab-content-timeline-line-background"></div>
                        <div class="growfund-update-tab-content-timeline-line-progress"></div>
                    <div class="growfund-update-tab-content-timeline-dot-current"></div>
                    <div class="growfund-update-tab-content-timeline-progress-text">
                        <span class="growfund-update-tab-content-timeline-progress-count"></span>
                        <span class="growfund-update-tab-content-timeline-current-date"></span>
                    </div>
                    <div class="growfund-update-tab-content-timeline-point"></div>
                </div>
                <div class="growfund-update-tab-content-timeline-end">
                    <span class="growfund-update-tab-content-timeline-date"></span>
                </div>
            </div>
        </div>
    </div>
    <div class="growfund-update-load-more growfund-hidden">
        <?php esc_html_e('Load more updates...', 'growfund'); ?>
    </div>   
    </div> 
</div>
