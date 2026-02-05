<?php

namespace Growfund\Views\Components;

use Growfund\View;

defined( 'ABSPATH' ) || exit;

class PaymentMethodCard extends View {

    /** @var array */
    public $payment_methods;

    /** @var string */
    public $error_msg;

    protected function get_template_dir() {
        return 'site/components';
    }

	protected function enqueue_styles() {
        wp_enqueue_style(
			'growfund-payment-method-card-styles',
			GROWFUND_DIR_URL . 'resources/assets/site/styles/components/payment-method-card.css',
			['growfund-main-styles'],
			GROWFUND_VERSION
        );
    }
}
