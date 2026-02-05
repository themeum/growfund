<?php
/**
 * @var Growfund\Views\Components\Campaign\CampaignCard $campaign_card
 */


defined( 'ABSPATH' ) || exit;

use Growfund\Supports\CampaignGoal;
use Growfund\Supports\Date;
use Growfund\Supports\User;
use Growfund\Views\Components\Campaign\CampaignBookmarkButton;
use Growfund\Views\Components\UI\Avatar;

if (empty($campaign_card->campaign)) {
    return;
}   

$growfund_campaign_goal_percentage = CampaignGoal::goal_achieved_percentage($campaign_card->campaign);
$growfund_campaign_goal_info = CampaignGoal::get_goal_info_for_display($campaign_card->campaign);
$growfund_campaign_author_avatar = !empty($campaign_card->campaign->author_id) ? User::get_avatar_image($campaign_card->campaign->author_id) : null;


$growfund_campaign_image = $campaign_card->campaign->images[0]['url'] ?? null;
$growfund_campaign_sub_description = substr($campaign_card->campaign->description, 0, 300);
$growfund_campaign_url = growfund_campaign_url($campaign_card->campaign->slug ?? $campaign_card->campaign->id);

?>

<div class="growfund-campaign-card-wrapper">
    <div class="growfund-campaign-card">
        <a href="<?php echo esc_url($growfund_campaign_url); ?>" class="growfund-campaign-card-link">
            <div class="growfund-campaign-card-image">
                <?php if (!empty($growfund_campaign_image)) { ?>
                    <img 
                        src="<?php echo esc_url($campaign_card->campaign->images[0]['url']); ?>" 
                        alt="<?php echo esc_attr($campaign_card->campaign->title); ?>" 
                        class="growfund-campaign-thumbnail" 
                    />
                <?php } else { ?>
                    <div class="growfund-campaign-card-image-placeholder">
                        <span><?php echo esc_html(ucfirst(substr($campaign_card->campaign->title, 0, 1))); ?></span>
                    </div>
                <?php } ?>
                
                <?php 
                if (!growfund_user()->is_admin()) {
                    $growfund_bookmark_button = new CampaignBookmarkButton();
                    $growfund_bookmark_button->campaign = $campaign_card->campaign;
                    $growfund_bookmark_button->classname = 'growfund-campaign-card-bookmark-button';
                    $growfund_bookmark_button->has_label = false;

                    growfund_render($growfund_bookmark_button);
                }
                ?>
            </div>
        </a>

        <div class="growfund-campaign-card-content">
            <div class="growfund-campaign-card-creator-info">
                <?php 
                    $growfund_avatar = new Avatar();

                    $growfund_avatar->avatar_name = $campaign_card->campaign->created_by ?? ''; 
                    $growfund_avatar->use_acronym = true; 
                    $growfund_avatar->src = $growfund_campaign_author_avatar['url'] ?? '';
                    $growfund_avatar->classname = 'growfund-campaign-card-creator-avatar';

                    growfund_render($growfund_avatar);
                ?>

                <span class="growfund-campaign-card-creator-name"><?php echo esc_html($campaign_card->campaign->created_by ?? ''); ?></span>
                <?php if (!empty($campaign_card->campaign->end_date)) : ?>
                    <span class="growfund-campaign-card-time-left">
                        <?php echo esc_html(Date::human_readable_time_diff($campaign_card->campaign->end_date)); ?>
                    </span>
                <?php endif; ?>
            </div>

            <a href="<?php echo esc_url($growfund_campaign_url); ?>" class="growfund-campaign-card-link">
                <h3 class="growfund-campaign-card-title"><?php echo esc_html($campaign_card->campaign->title); ?></h3>
            </a>

            
            <div class="growfund-campaign-card-goal-progress">
                <div class="growfund-campaign-card-progress-bar">
                    <div 
                        class="growfund-campaign-card-progress-fill" 
                        style="width: <?php echo esc_attr($growfund_campaign_goal_percentage); ?>%;"
                    ></div>
                </div>
                <div class="growfund-campaign-card-fund-amount">
                    <?php echo esc_html($growfund_campaign_goal_info); ?>
                </div>
            </div>

           
            <div class="growfund-campaign-card-description">
                <div class="growfund-campaign-card-description-border-top"></div>
                <p class="growfund-campaign-card-description-content">
                    <?php echo esc_html($growfund_campaign_sub_description) . (strlen($growfund_campaign_sub_description) > 300 ? '...' : ''); ?>
                </p>
            </div>
            
        </div>
    </div>
</div>



