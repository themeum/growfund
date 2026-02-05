<?php
/**
 * @var Growfund\Views\Components\Campaign\CampaignRewardCard $campaign_reward_card
 */

defined( 'ABSPATH' ) || exit;

use Growfund\Views\Components\Campaign\CampaignRewardContent;
use Growfund\Views\Components\Campaign\RewardPledgeButton;
use Growfund\Views\Components\UI\Image;

?>

<div class="growfund-campaign-reward-card">
    <div class="growfund-campaign-reward-card-image">
        <?php 
            
            $growfund_reward_image = new Image([
                'src'   => $campaign_reward_card->reward->image['url'] ?? growfund_placeholder_image_url(),
                'alt'   => $campaign_reward_card->reward->title,
            ]);

            growfund_render($growfund_reward_image);
			?>
    </div>
    <?php
		$growfund_reward_content = new CampaignRewardContent();
        $growfund_reward_content->reward = $campaign_reward_card->reward;
        $growfund_reward_content->campaign = $campaign_reward_card->campaign;
        $growfund_reward_content->classname = 'growfund-campaign-reward-card-padded';
        
        growfund_render ($growfund_reward_content);
    ?>
    <div class="growfund-campaign-reward-card-pledge-button">
        <?php
		$growfund_pledge_button = new RewardPledgeButton();
        $growfund_pledge_button->reward = $campaign_reward_card->reward;
        $growfund_pledge_button->campaign = $campaign_reward_card->campaign;
        
        growfund_render($growfund_pledge_button);
		?>

    </div>
</div>

