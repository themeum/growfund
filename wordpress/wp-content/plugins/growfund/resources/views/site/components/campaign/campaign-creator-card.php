<?php
/** @var Growfund\Views\Components\Campaign\CampaignCreatorCard $campaign_creator_card */

use Growfund\Views\Components\UI\Avatar;

defined( 'ABSPATH' ) || exit;

?>
    <div class="growfund-campaign-creator-card">
        <div class="growfund-campaign-creator-card-avatar">
            <?php
				$growfund_avatar = new Avatar();
                $growfund_avatar->use_acronym = true;
                $growfund_avatar->avatar_name = "Admin";
                $growfund_avatar->src = $campaign_creator_card->avatar_src;
                $growfund_avatar->classname = $campaign_creator_card->avatar_class;
				growfund_render($growfund_avatar);
			?>
        </div>
        <div class="growfund-campaign-creator-card-details">
            <h4 class="growfund-campaign-creator-card-name"><?php echo esc_html($campaign_creator_card->display_name); ?></h4>
            <p class="growfund-campaign-creator-card-stats">
                <?php /* translators: %s: total campaign created */ ?>
                <?php printf(esc_html__('%s created ', 'growfund'), esc_html($campaign_creator_card->total_campaign_created)); ?> 
                • 
                <?php /* translators: %s: total number of contributions */ ?>
                <?php printf(esc_html__(' %s backed', 'growfund'), esc_html($campaign_creator_card->total_number_of_contributions)); ?> 
            </p>
        </div>
    </div>



       

    
