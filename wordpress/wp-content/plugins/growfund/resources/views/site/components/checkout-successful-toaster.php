<?php
/**
 * @var Growfund\Views\Components\CheckoutSuccessfulToaster $checkout_successful_toaster
 */

use Growfund\Supports\Currency;
use Growfund\Views\Components\Form\Button;

defined( 'ABSPATH' ) || exit;

?>

<div class="growfund-checkout-successful-toaster-container">
        <div class="growfund-checkout-successful-toaster-icon-wrapper">
            <div class ="growfund-checkout-successful-toaster-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" fill="none"><path fill="#23A26D" d="M16 2.667C8.653 2.667 2.667 8.654 2.667 16c0 7.347 5.986 13.334 13.333 13.334S29.333 23.347 29.333 16c0-7.346-5.986-13.333-13.333-13.333Zm6.373 10.267-7.56 7.56a1 1 0 0 1-1.413 0L9.627 16.72a1.006 1.006 0 0 1 0-1.413 1.006 1.006 0 0 1 1.413 0l3.067 3.067 6.853-6.854a1.006 1.006 0 0 1 1.413 0 1.006 1.006 0 0 1 0 1.414Z"/></svg>
            </div>
        </div>
        <div class="growfund-checkout-successful-toaster-title-wrapper">
            <span class="growfund-checkout-successful-toaster-title">
                <?php 
                    echo $checkout_successful_toaster->confirmation_title 
                        ? esc_html($checkout_successful_toaster->confirmation_title) 
                        : esc_html__('Pledge Successful!', 'growfund'); 
				?>
            </span>
            <p class="growfund-checkout-successful-toaster-subtitle">
                <?php 
                    echo $checkout_successful_toaster->confirmation_description 
                        ? esc_html($checkout_successful_toaster->confirmation_description) 
                        : esc_html__('Thank you for backing this campaign. Your support brings the project closer to life.', 'growfund'); 
                ?>
            </p>
        </div>

        <div class="growfund-checkout-successful-toaster-amount-group">
            <span class="growfund-checkout-successful-toaster-amount-label"><?php esc_html_e('Total Pledged Campaign', 'growfund'); ?></span>
            <div class="growfund-checkout-successful-toaster-amount-value"><?php echo esc_html(Currency::format($checkout_successful_toaster->pledge->payment->total ?? 0)); ?></div>
        </div>

        <div class="growfund-checkout-successful-toaster-section-wrapper">
            <div class="growfund-checkout-successful-toaster-section-header">
            <span class="growfund-checkout-successful-toaster-section-icon"><?php growfund_echo_safe_html($checkout_successful_toaster->get_svg_icon('assets/site/icon/user.svg')); ?></span> 
            <span class="growfund-checkout-successful-toaster-section-title"><?php esc_html_e('Campaign Details', 'growfund'); ?></span> 
            </div>
            <div class="growfund-checkout-successful-toaster-wrapper">
                <div class="growfund-checkout-successful-toaster-info-card">
                <span class="growfund-checkout-successful-toaster-card-label"><?php esc_html_e('Campaign Title', 'growfund'); ?></span>
                <span class="growfund-checkout-successful-toaster-card-value"><?php echo esc_html($checkout_successful_toaster->pledge->campaign->title); ?></span>
                </div>
            <div class="growfund-checkout-successful-toaster-info-card">
                <span class="growfund-checkout-successful-toaster-card-label"><?php esc_attr_e('Reward Tier', 'growfund'); ?></span>
                <span class="growfund-checkout-successful-toaster-card-value"><?php echo !empty($checkout_successful_toaster->pledge->reward) ? 'With rewards' : 'Without rewards'; ?></span>
            </div>
            <div class="growfund-checkout-successful-toaster-info-card">
                <span class="growfund-checkout-successful-toaster-card-label"><?php esc_html_e('Ref Number', 'growfund'); ?></span>
                <span class="growfund-checkout-successful-toaster-card-value"><?php echo esc_html($checkout_successful_toaster->pledge->id); ?></span>
            </div>
            <div class="growfund-checkout-successful-toaster-info-card">
                <span class="growfund-checkout-successful-toaster-card-label"><?php esc_html_e('Payment Method', 'growfund'); ?></span>
                <span class="growfund-checkout-successful-toaster-card-value"><?php echo esc_html($checkout_successful_toaster->pledge->payment->payment_method->label); ?></span>
            </div>
        </div>

        <div class="growfund-checkout-successful-toaster-section-header">
            <span class="growfund-checkout-successful-toaster-section-icon"><?php growfund_echo_safe_html($checkout_successful_toaster->get_svg_icon('assets/site/icon/megaphone.svg')); ?></span> 
            <span class="growfund-checkout-successful-toaster-section-title"><?php esc_html_e('Backer Details', 'growfund'); ?></span>
        </div>
        <div class="growfund-checkout-successful-toaster-wrapper">
            <div class="growfund-checkout-successful-toaster-info-card">
                <span class="growfund-checkout-successful-toaster-card-label"><?php esc_html_e('Backer Name', 'growfund'); ?></span>
                <span class="growfund-checkout-successful-toaster-card-value"><?php printf('%s %s', esc_html($checkout_successful_toaster->pledge->backer->first_name), esc_html($checkout_successful_toaster->pledge->backer->last_name)); ?></span>
            </div>
            <div class="growfund-checkout-successful-toaster-info-card">
                <span class="growfund-checkout-successful-toaster-card-label"><?php esc_html_e('Email Address', 'growfund'); ?></span>
                <span class="growfund-checkout-successful-toaster-card-value"><?php echo esc_html($checkout_successful_toaster->pledge->backer->email); ?></span>
            </div>
        </div>
        </div>

        <?php if (growfund_support_future_payment($checkout_successful_toaster->pledge->payment->payment_method->name ?? '')) : ?>
        <div class="growfund-checkout-successful-toaster-funding-note-wrapper">
            <span><?php growfund_echo_safe_html($checkout_successful_toaster->get_svg_icon('assets/site/icon/check.svg')); ?></span>
            <span class="growfund-checkout-successful-toaster-funding-note"><?php esc_html_e("You'll be charged only if the campaign is successfully funded.", 'growfund'); ?></span>
        
        </div>
        <?php endif; ?>
        <?php
            $growfund_dashboard_button = new Button();
            $growfund_dashboard_button->label = "Back to Dashboard";
            $growfund_dashboard_button->classname = "growfund-checkout-successful-toaster-back-button";
            $growfund_dashboard_button->svg_icon = "assets/site/icon/chevron-left.svg";
            $growfund_dashboard_button->icon_position = 'left';
            $growfund_dashboard_button->has_link = true;
            $growfund_dashboard_button->href = growfund_user_dashboard_url();
            growfund_render($growfund_dashboard_button);
		?>

</div>
    
	
            
