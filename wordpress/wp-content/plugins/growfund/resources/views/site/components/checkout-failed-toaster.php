<?php
/**
 * @var Growfund\Views\Components\CheckoutFailedToaster $checkout_failed_toaster
 */

use Growfund\Views\Components\Form\Button;

defined( 'ABSPATH' ) || exit;

?>

<div class="growfund-checkout-failed-toaster-container">
    <div class="growfund-checkout-failed-toaster-icon-wrapper">
        <div class="growfund-checkout-failed-toaster-icon">
        <?php growfund_echo_safe_html($checkout_failed_toaster->get_svg_icon('assets/site/icon/cross-2.svg')); ?>
        </div>
    </div>

    <div class="growfund-checkout-failed-toaster-title-wrapper">
        <span class="growfund-checkout-failed-toaster-title">
            <?php 
            growfund_app()->is_donation_mode() 
                ? esc_html_e('Failed to Donate', 'growfund')
                : esc_html_e('Failed to Pledge', 'growfund');
            ?>
        </span>
        <p class="growfund-checkout-failed-toaster-subtitle"><?php esc_html_e('Unfortunately, your payment could not be processed. Please try again', 'growfund'); ?></p>
    </div>
    <div class="growfund-checkout-failed-toaster-button-wrapper">
        <?php
        $growfund_try_button = new Button();
        $growfund_try_button->classname = "growfund-checkout-failed-toaster-try-button";
        $growfund_try_button->label = __("Try Again", 'growfund');
        $growfund_try_button->icon_position = 'left';
        $growfund_try_button->svg_icon = "assets/site/icon/reload.svg";
        $growfund_try_button->has_link = true;
        $growfund_try_button->href = growfund_campaign_url($checkout_failed_toaster->campaign_id);

        growfund_render($growfund_try_button);

        $growfund_contact_button = new Button();
        $growfund_contact_button->classname = "growfund-checkout-failed-toaster-contact-button";
        $growfund_contact_button->label = __("Contact Support", 'growfund');
        $growfund_contact_button->has_link = true;
        $growfund_contact_button->href = 'https://growfund.com/support';
        growfund_render($growfund_contact_button);
        ?>

    </div>
</div>