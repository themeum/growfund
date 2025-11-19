<?php

namespace Growfund\Hooks\Actions\Woocommerce;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\HookNames;
use Growfund\Constants\HookTypes;
use Growfund\Hooks\BaseHook;
use Growfund\Supports\Woocommerce;
use Growfund\Supports\WoocommerceToNative;

class NewOrder extends BaseHook
{
    public function get_name()
    {
        return HookNames::WC_CHECKOUT_ORDER_PROCESSED;
    }

    public function get_type()
    {
        return HookTypes::ACTION;
    }

    public function handle(...$args)
    {
        if (!Woocommerce::is_native_checkout()) {
            return;
        }

        $order_id = $args[0];

        if (growfund_app()->is_donation_mode()) {
            return WoocommerceToNative::create_donation_from_order($order_id);
        }

        WoocommerceToNative::create_pledge_from_order($order_id);
    }
}
