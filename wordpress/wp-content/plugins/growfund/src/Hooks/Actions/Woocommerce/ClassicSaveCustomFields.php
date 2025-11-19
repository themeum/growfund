<?php

namespace Growfund\Hooks\Actions\Woocommerce;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\HookNames;
use Growfund\Constants\HookTypes;
use Growfund\Hooks\BaseHook;
use Growfund\Sanitizer;
use Growfund\Supports\Woocommerce;

class ClassicSaveCustomFields extends BaseHook
{
    public function get_name()
    {
        return HookNames::WC_CLASSIC_CHECKOUT_CREATE_ORDER;
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

        $order = $args[0];

        if (isset($_POST[growfund_with_prefix('bonus_support_amount')])) { // phpcs:ignore
            $bonus_support_amount = Sanitizer::apply_rule($_POST[growfund_with_prefix('bonus_support_amount')], Sanitizer::FLOAT); // phpcs:ignore
            $order->update_meta_data(growfund_with_prefix('bonus_support_amount'), $bonus_support_amount);
        }

        if (isset($_POST[growfund_with_prefix('donation_amount')])) { // phpcs:ignore
            $donation_amount = Sanitizer::apply_rule($_POST[growfund_with_prefix('donation_amount')], 'float'); // phpcs:ignore
            $order->update_meta_data(growfund_with_prefix('donation_amount'), $donation_amount);
        }

        if (isset($_POST[growfund_with_prefix('is_anonymous')])) { // phpcs:ignore
            $is_anonymous = Sanitizer::apply_rule($_POST[growfund_with_prefix('is_anonymous')], 'int'); // phpcs:ignore
            $order->update_meta_data(growfund_with_prefix('is_anonymous'), $is_anonymous);
        }

        if (isset($_POST[growfund_with_prefix('fund_id')])) { // phpcs:ignore
            $fund_id = Sanitizer::apply_rule($_POST[growfund_with_prefix('fund_id')], 'int'); // phpcs:ignore
            $order->update_meta_data(growfund_with_prefix('fund_id'), $fund_id);
        }
    }
}
