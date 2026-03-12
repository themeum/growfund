<?php
/**
 * @var Growfund\Views\Components\Campaign\CampaignPledgeModal $campaign_pledge_modal
 */

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\Campaign\AppreciationType;
use Growfund\Supports\Utils;
use Growfund\Views\Components\Campaign\CampaignRewardContent;
use Growfund\Views\Components\Campaign\RewardPledgeButton;
use Growfund\Views\Components\Form\Button;
use Growfund\Views\Components\Form\NumberField;
use Growfund\Views\Components\UI\Image;

?>


<div class="growfund-campaign-pledge-modal">
    <?php
	if (
            $campaign_pledge_modal->campaign->appreciation_type === AppreciationType::GIVING_THANKS ||
            ($campaign_pledge_modal->campaign->appreciation_type === AppreciationType::GOODIES && $campaign_pledge_modal->campaign->allow_pledge_without_reward)
        ) :
		?>
        <form class="growfund-campaign-pledge-modal-form" method="GET" action="<?php echo esc_url(Utils::get_checkout_url($campaign_pledge_modal->campaign->id)); ?>">
            <span class="growfund-campaign-pledge-modal-form-title"><?php esc_html_e('Pledge without a reward', 'growfund'); ?></span>
            
            <div class="growfund-campaign-pledge-modal-form-wrapper">
            <input type="hidden" name="campaign_id" value="<?php echo esc_attr($campaign_pledge_modal->campaign->id); ?>" />
			<?php
				$growfund_field = new NumberField();
				$growfund_field->classname = 'growfund-campaign-pledge-modal-input';
				$growfund_field->placeholder = '0.00';
				$growfund_field->name = 'amount';
				$growfund_field->id = 'growfund-campaign-pledge-modal-amount-input';
				$growfund_field->show_currency = true;
				$growfund_field->label = __('Pledge Amount', 'growfund');
				growfund_render($growfund_field);

				$growfund_pledge_button = new Button();
				$growfund_pledge_button->classname = 'growfund-campaign-pledge-modal-btn growfund-branding-btn';
				$growfund_pledge_button->id="growfund-campaign-pledge-modal-btn";
				$growfund_pledge_button->label = __('Pledge', 'growfund');
                $growfund_pledge_button->type = 'submit';

				growfund_render($growfund_pledge_button);
			?>
            </div>
        </form>
    <?php endif; ?>
    <?php if ($campaign_pledge_modal->campaign->appreciation_type === AppreciationType::GOODIES) : ?>
        <div class="growfund-campaign-pledge-modal-rewards-collection">
            <span class="growfund-campaign-pledge-modal-reward-section-title"><?php esc_html_e('Available rewards', 'growfund'); ?></span>
            <?php foreach ($campaign_pledge_modal->rewards as $growfund_reward) : ?>
            
                <div class="growfund-campaign-pledge-modal-reward-card">
                    <?php
                        $growfund_reward_content = new CampaignRewardContent();
                        $growfund_reward_content->campaign = $campaign_pledge_modal->campaign;
                        $growfund_reward_content->reward = $growfund_reward;

                        growfund_render($growfund_reward_content);
                    ?>
                    <div class="growfund-campaign-pledge-modal-reward-card-image-wrapper">
                        <div class="growfund-campaign-pledge-modal-reward-card-image">

                            <?php 
                        
                            $growfund_reward_image = new Image([
                                'src'   => $growfund_reward->image['url'] ?? growfund_placeholder_image_url(),
                                'alt'   => $growfund_reward->title,
                                'class' => "growfund-campaign-pledge-modal-reward-card-image"
                            ]);
                        
                            growfund_render($growfund_reward_image);
                            ?>
                        </div>
                
                        <?php
                        $growfund_pledge_button = new RewardPledgeButton();
                        $growfund_pledge_button->campaign = $campaign_pledge_modal->campaign;
                        $growfund_pledge_button->reward = $growfund_reward;

                        growfund_render($growfund_pledge_button);
                        ?>
                    </div>
                
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
    
