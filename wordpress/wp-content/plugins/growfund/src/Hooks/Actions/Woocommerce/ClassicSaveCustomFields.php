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

        $bonus_support_amount = growfund_input_post(growfund_with_prefix('bonus_support_amount'), null, Sanitizer::FLOAT);

        if (!is_null($bonus_support_amount)) {
            $order->update_meta_data(growfund_with_prefix('bonus_support_amount'), $bonus_support_amount);
        }

        $donation_amount = growfund_input_post(growfund_with_prefix('donation_amount'), null, Sanitizer::FLOAT);

        if (!is_null($donation_amount)) {
            $order->update_meta_data(growfund_with_prefix('donation_amount'), $donation_amount);
        }

        $is_anonymous = growfund_input_post(growfund_with_prefix('is_anonymous'), null, Sanitizer::INT);

        if (!is_null($is_anonymous)) {
            $order->update_meta_data(growfund_with_prefix('is_anonymous'), $is_anonymous);
        }

        $fund_id = growfund_input_post(growfund_with_prefix('fund_id'), null, Sanitizer::INT);

        if (!is_null($fund_id)) {
            $order->update_meta_data(growfund_with_prefix('fund_id'), $fund_id);
        }
    }
}
