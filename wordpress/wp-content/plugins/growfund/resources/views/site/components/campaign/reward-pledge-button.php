<?php
/**
 * @var Growfund\Views\Components\Campaign\RewardPledgeButton $reward_pledge_button
 */

use Growfund\Supports\Currency;
use Growfund\Supports\Utils;
use Growfund\Views\Components\Form\Button;

defined( 'ABSPATH' ) || exit;

?>

<div 
    class="growfund-reward-pledge-button-container <?php echo esc_attr( $reward_pledge_button->classname ?? '' ); ?>"
    data-campaign-id="<?php echo esc_attr( $reward_pledge_button->campaign->id ); ?>"
    data-reward-id="<?php echo esc_attr( $reward_pledge_button->reward->id ); ?>"
    data-reward-amount="<?php echo esc_attr( $reward_pledge_button->reward->amount ); ?>"
>
    <form method="GET" action="<?php echo esc_url(Utils::get_checkout_url($reward_pledge_button->campaign->id)); ?>" class="growfund-reward-pledge-button-form">
        <input hidden type="text" value="<?php echo esc_attr($reward_pledge_button->reward->amount); ?>" name="amount" />
        <input hidden type="text" value="<?php echo esc_attr($reward_pledge_button->reward->id); ?>" name="reward_id" />
        <input hidden type="text" value="<?php echo esc_attr($reward_pledge_button->campaign->id); ?>" name="campaign_id" />
    <?php
		$growfund_pledge_button = new Button();
        $growfund_pledge_button->type = 'submit';
		$growfund_pledge_button->classname = 'growfund-reward-pledge-button growfund-branding-btn';
		$growfund_pledge_button->label = sprintf(
            /* translators: %s: pledge amount */
            __( 'Pledge %s', 'growfund' ),
            Currency::format($reward_pledge_button->reward->amount ?? 0)
        );
        $growfund_pledge_button->disabled = !$reward_pledge_button->is_pledge_allowed_for_this_campaign() || !$reward_pledge_button->is_pledge_allowed_without_reward();
		?>

    <?php growfund_render( $growfund_pledge_button ); ?>
    </form>
</div>