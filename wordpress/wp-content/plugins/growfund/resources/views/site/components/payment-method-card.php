<?php
/**
 * @var Growfund\Views\Components\PaymentMethodCard $payment_method_card
 */

use Growfund\Views\Components\Form\RadioField;
use Growfund\Views\Components\UI\Image;

defined( 'ABSPATH' ) || exit;


?>

<div class="growfund-payment-method-card">
    <div class="growfund-payment-method-card-title-wrapper">
    <span class="growfund-payment-method-card-title">
        <?php esc_html_e('Payment method', 'growfund'); ?>
    </span>

    <?php if ($payment_method_card->error_msg) : ?>
        <span class="growfund-form-error"><?php echo esc_html($payment_method_card->error_msg); ?></span>
    <?php endif; ?>
    </div>
    <div class="growfund-payment-method-card-wrapper">
        <?php
        foreach ($payment_method_card->payment_methods as $growfund_index => $growfund_payment_method) {
            $growfund_payment_radio = new RadioField();

            $growfund_payment_radio->name    = 'payment_method';
            $growfund_payment_radio->classname   = 'growfund-payment-method-card-radio-input';
            $growfund_payment_radio->label   = $growfund_payment_method->config['label'] ?? $growfund_payment_method->config['title'];
            $growfund_payment_radio->value   = $growfund_payment_method->name;
            $growfund_payment_radio->checked = $growfund_index === 0;
            $growfund_payment_radio->wrapper_class = 'growfund-payment-method-card-option-item';

            $growfund_payment_logo = $growfund_payment_method->config['logo'] ?? growfund_placeholder_image_url();

            $growfund_payment_radio->icon =  growfund_get_html(
                new Image([
                    'src' => is_string($growfund_payment_logo) ? $growfund_payment_logo : $growfund_payment_logo['url'] ?? '',
                    'style' => 'height: 24px;'
                ])
            );

            growfund_render($growfund_payment_radio);
        }
        ?>
    </div>
</div>
