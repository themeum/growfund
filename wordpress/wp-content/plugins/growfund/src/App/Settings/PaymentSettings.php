<?php

namespace Growfund\App\Settings;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\AppConfigKeys;
use Growfund\Constants\FeeType;
use Growfund\Constants\HookNames;
use Growfund\Constants\PaymentEngine;
use Growfund\Core\AppSettings;
use Growfund\Payments\Constants\PaymentGatewayType;
use Growfund\Payments\DTO\PaymentGatewayDTO;
use Growfund\Supports\Arr;
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

        return array_map(function ($gateway){
            if ($gateway['type'] === PaymentGatewayType::ONLINE) {

                if ($gateway['name'] === 'growfund-gateway-paypal') {
                    $gateway['is_installed'] = true;
                    return PaymentGatewayDTO::from_array($gateway);
                }

                $gateway['is_installed'] = growfund_is_plugin_activated(sprintf('%s/%s.php', $gateway['name'], $gateway['name']));

                return PaymentGatewayDTO::from_array($gateway);
            }
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
        return apply_filters(HookNames::GROWFUND_ALLOW_GUEST_CHECKOUT_FILTER, false, $this->settings); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
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

    public function get_minimum_balance_to_request_withdrawal()
    {
        return $this->get('minimum_balance_to_request_withdrawal');
    }

	public function is_enabled_platform_fee()
    {
        return (bool) $this->get('enable_platform_fee', false);
    }

    public function get_platform_fee()
    {
        return (int) $this->get('platform_fee', 0);
    }

    public function get_platform_fee_type()
    {
        return $this->get('platform_fee_type', FeeType::PERCENTAGE);
    }
}
