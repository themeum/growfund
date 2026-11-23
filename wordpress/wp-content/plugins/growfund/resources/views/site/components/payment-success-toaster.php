<?php
/**
 * @var Growfund\Views\Components\PaymentSuccessToaster $payment_success_toaster
 */

use Growfund\Constants\DateTimeFormats;
use Growfund\Payments\Constants\PaymentGatewayType;
use Growfund\Supports\Currency;
use Growfund\Supports\Date;
use Growfund\Views\Components\Form\Button;

defined( 'ABSPATH' ) || exit;

?>

<div class="growfund-payment-success-toaster-container">
    <div class="growfund-payment-success-toaster-icon-wrapper">
        <?php if ($payment_success_toaster->donation->payment_method->type === PaymentGatewayType::ONLINE) : ?>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" fill="none"><path fill="#23A26D" d="M16 2.667C8.653 2.667 2.667 8.654 2.667 16c0 7.347 5.986 13.334 13.333 13.334S29.333 23.347 29.333 16c0-7.346-5.986-13.333-13.333-13.333Zm6.373 10.267-7.56 7.56a1 1 0 0 1-1.413 0L9.627 16.72a1.006 1.006 0 0 1 0-1.413 1.006 1.006 0 0 1 1.413 0l3.067 3.067 6.853-6.854a1.006 1.006 0 0 1 1.413 0 1.006 1.006 0 0 1 0 1.414Z"/></svg>
        <?php else : ?>
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="none"><path fill="#f0b400" d="M6.575 25.425c2.5 2.5 5.89 3.906 9.425 3.91a13.38 13.38 0 0 0 9.168-3.669v2.335a1.334 1.334 0 1 0 2.667 0v-6a1.334 1.334 0 0 0-1.334-1.334h-6a1.334 1.334 0 0 0 0 2.667h3.235A10.66 10.66 0 0 1 5.332 16a1.334 1.334 0 0 0-2.667 0 13.35 13.35 0 0 0 3.91 9.425"/><path fill="#f0b400" fill-rule="evenodd" d="M5.499 11.333h6a1.334 1.334 0 0 0 0-2.667H8.265a10.662 10.662 0 0 1 18.404 7.333 1.333 1.333 0 1 0 2.667 0A13.328 13.328 0 0 0 6.832 6.326V4a1.334 1.334 0 0 0-2.667 0v6A1.333 1.333 0 0 0 5.5 11.333" clip-rule="evenodd"/></svg>
        <?php endif; ?>
    </div>
    
    <div class="growfund-payment-success-toaster-title-wrapper">
        <span class="growfund-payment-success-toaster-title">
            <?php 
			if ($payment_success_toaster->donation->payment_method->type === PaymentGatewayType::ONLINE) {
				echo esc_html(
					$payment_success_toaster->confirmation_title 
						? $payment_success_toaster->confirmation_title 
						: __('Payment Success!', 'growfund')
				); 
			} else {
                echo esc_html__('Payment in Progress', 'growfund');     
			}
			?>
        </span>
        <p class="growfund-payment-success-toaster-subtitle">
            <?php 
			if ($payment_success_toaster->donation->payment_method->type === PaymentGatewayType::ONLINE) {
				echo esc_html(
					$payment_success_toaster->confirmation_description 
						? $payment_success_toaster->confirmation_description 
						: __('Your payment has been successfully done.', 'growfund')
					) ; 
			} else {
				echo esc_html__('Your payment is being processed. We\'ll email you when it\'s complete.', 'growfund'); 
			}
			?>
        </p>
    </div>

    <div class="growfund-payment-success-toaster-amount-group">
        <span class="growfund-payment-success-toaster-amount-label"><?php esc_html_e('Total Payment', 'growfund'); ?></span>
        <div class="growfund-payment-success-toaster-amount-value">
            <?php echo esc_html(Currency::format($payment_success_toaster->donation->amount ?? 0)); ?>
        </div>
    </div>

   
        <div class="growfund-payment-success-toaster-wrapper">
            <div class="growfund-payment-success-toaster-info-card">
                <span class="growfund-payment-success-toaster-card-label"><?php esc_html_e('Ref Number', 'growfund'); ?></span>
                <span class="growfund-payment-success-toaster-card-value"><?php echo esc_html($payment_success_toaster->donation->id); ?></span>
            </div>
            <div class="growfund-payment-success-toaster-info-card">
                <span class="growfund-payment-success-toaster-card-label"><?php esc_html_e('Payment Time', 'growfund'); ?></span>
                <span class="growfund-payment-success-toaster-card-value" data-growfund-datetime="<?php echo esc_attr($payment_success_toaster->donation->created_at); ?>">
                    <?php echo esc_html(Date::format($payment_success_toaster->donation->created_at, DateTimeFormats::HUMAN_READABLE_DATE)); ?>
                </span>
            </div>
            <div class="growfund-payment-success-toaster-info-card">
                <span class="growfund-payment-success-toaster-card-label"><?php esc_html_e('Payment Method', 'growfund'); ?></span>
                <span class="growfund-payment-success-toaster-card-value">
                    <?php echo esc_html($payment_success_toaster->donation->payment_method->label ?? ''); ?>
                </span>
            </div>
            <div class="growfund-payment-success-toaster-info-card">
                <span class="growfund-payment-success-toaster-card-label"><?php esc_html_e('Sender Name', 'growfund'); ?></span>
                <span class="growfund-payment-success-toaster-card-value">
                    <?php 
                    printf(
                        '%s %s', 
                        esc_html($payment_success_toaster->donation->donor->first_name ?? ''), 
                        esc_html($payment_success_toaster->donation->donor->last_name ?? '')
                    ); 
					?>
                </span>
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