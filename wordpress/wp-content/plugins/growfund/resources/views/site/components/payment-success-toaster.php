<?php
/**
 * @var Growfund\Views\Components\PaymentSuccessToaster $payment_success_toaster
 */

use Growfund\Constants\DateTimeFormats;
use Growfund\Supports\Currency;
use Growfund\Supports\Date;
use Growfund\Views\Components\Form\Button;

defined( 'ABSPATH' ) || exit;


?>


<div class="growfund-payment-success-toaster-container">
    <div class="growfund-payment-success-toaster-icon-wrapper">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" fill="none"><path fill="#23A26D" d="M16 2.667C8.653 2.667 2.667 8.654 2.667 16c0 7.347 5.986 13.334 13.333 13.334S29.333 23.347 29.333 16c0-7.346-5.986-13.333-13.333-13.333Zm6.373 10.267-7.56 7.56a1 1 0 0 1-1.413 0L9.627 16.72a1.006 1.006 0 0 1 0-1.413 1.006 1.006 0 0 1 1.413 0l3.067 3.067 6.853-6.854a1.006 1.006 0 0 1 1.413 0 1.006 1.006 0 0 1 0 1.414Z"/></svg>
    </div>
    
    <div class="growfund-payment-success-toaster-title-wrapper">
        <span class="growfund-payment-success-toaster-title">
            <?php 
                echo $payment_success_toaster->confirmation_title 
                    ? esc_html($payment_success_toaster->confirmation_title) 
                    : esc_html__('Payment Success!', 'growfund'); 
            ?>
        </span>
        <p class="growfund-payment-success-toaster-subtitle">
            <?php 
                echo $payment_success_toaster->confirmation_description 
                    ? esc_html($payment_success_toaster->confirmation_description) 
                    : esc_html__('Your payment has been successfully done.', 'growfund'); 
            ?>
        </p>
    </div>

    <div class="growfund-payment-success-toaster-amount-group">
        <span class="growfund-payment-success-toaster-amount-label"><?php esc_html_e('Total Payment', 'growfund'); ?></span>
        <div class="growfund-payment-success-toaster-amount-value"><?php echo esc_html(Currency::format($payment_success_toaster->donation->amount ?? 0)); ?></div>
    </div>

   
        <div class="growfund-payment-success-toaster-wrapper">
            <div class="growfund-payment-success-toaster-info-card">
                <span class="growfund-payment-success-toaster-card-label"><?php esc_html_e('Ref Number', 'growfund'); ?></span>
                <span class="growfund-payment-success-toaster-card-value"><?php echo esc_html($payment_success_toaster->donation->id); ?></span>
            </div>
            <div class="growfund-payment-success-toaster-info-card">
                <span class="growfund-payment-success-toaster-card-label"><?php esc_html_e('Payment Time', 'growfund'); ?></span>
                <span class="growfund-payment-success-toaster-card-value" data-growfund-datetime="<?php echo esc_attr($payment_success_toaster->donation->created_at); ?>"><?php echo esc_html(Date::format($payment_success_toaster->donation->created_at, DateTimeFormats::HUMAN_READABLE_DATE)); ?></span>
            </div>
            <div class="growfund-payment-success-toaster-info-card">
                <span class="growfund-payment-success-toaster-card-label"><?php esc_html_e('Payment Method', 'growfund'); ?></span>
                <span class="growfund-payment-success-toaster-card-value"><?php echo esc_html($payment_success_toaster->donation->payment_method->label ?? ''); ?></span>
            </div>
            <div class="growfund-payment-success-toaster-info-card">
                <span class="growfund-payment-success-toaster-card-label"><?php esc_html_e('Sender Name', 'growfund'); ?></span>
                <span class="growfund-payment-success-toaster-card-value"><?php printf('%s %s', esc_html($payment_success_toaster->donation->donor->first_name ?? ''), esc_html($payment_success_toaster->donation->donor->last_name ?? '')); ?></span>
            </div>
        </div>
        <?php
            $growfund_download_button = new Button();
            $growfund_download_button->label = "Get PDF Receipt";
            $growfund_download_button->classname = "growfund-payment-success-toaster-pdf-button";
            $growfund_download_button->svg_icon = "assets/site/icon/download.svg";
            $growfund_download_button->icon_position = 'left';
            $growfund_download_button->has_link = true;
            $growfund_download_button->href = growfund_donation_receipt_download_url($payment_success_toaster->donation->uid);

            growfund_render($growfund_download_button);
		?>
        <div class="growfund-payment-success-toaster-wave-bottom"></div>
</div>