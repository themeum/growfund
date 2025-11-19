<?php

namespace Growfund\App\Settings;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\AppConfigKeys;
use Growfund\Constants\PaymentEngine;
use Growfund\Core\AppSettings;
use Growfund\Payments\DTO\PaymentGatewayDTO;
use Growfund\Supports\Option;

class PaymentSettings extends AppSettings
{
    /**
     * Constructor - Initialize campaign settings from options.
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->settings = Option::get(AppConfigKeys::PAYMENT, null);
    }

    public function refresh()
    {
        $this->settings = Option::get(AppConfigKeys::PAYMENT, null);

        return $this;
    }

    /**
     * Retrieve installed payment gateways.
     *
     * @return PaymentGatewayDTO[]
     *
     * @since 1.0.0
     */
    public function get_installed_payment_gateways()
    {
        $gateways = $this->get('payments', []);

        if (empty($gateways)) {
            return [];
        }

        return array_map(function ($gateway) {
            return PaymentGatewayDTO::from_array($gateway);
        }, $gateways);
    }

    /**
     * Check if guest checkout is allowed.
     *
     * @return bool True if guest checkout is allowed, false otherwise.
     * @since 1.0.0
     */
    public function allow_guest_checkout()
    {
        return isset($this->settings['enable_guest_checkout'])
            ? filter_var($this->settings['enable_guest_checkout'], FILTER_VALIDATE_BOOLEAN)
            : false;
    }

    /**
     * Retrieve the current payment engine.
     *
     * @return string The current payment engine.
     * @since 1.0.0
     */
    public function get_payment_engine()
    {
        if (!growfund_app()->is_woocommerce_installed()) {
            return PaymentEngine::NATIVE;
        }
        
        return $this->get('e_commerce_engine', PaymentEngine::NATIVE);
    }

    /**
     * Returns the ID of the checkout page.
     *
     * @return int|null
     * @since 1.0.0
     */
    public function get_checkout_page_id()
    {
        if (isset($this->settings['checkout_page'])) {
            return (int) $this->settings['checkout_page'];
        }

        return null;
    }

    public function get_currency()
    {
        return $this->get('currency');
    }

    public function get_currency_position()
    {
        return $this->get('currency_position');
    }

    public function get_decimal_places()
    {
        return $this->get('decimal_places');
    }

    public function get_decimal_separator()
    {
        return $this->get('decimal_separator');
    }

    public function get_thousand_separator()
    {
        return $this->get('thousand_separator');
    }
}
