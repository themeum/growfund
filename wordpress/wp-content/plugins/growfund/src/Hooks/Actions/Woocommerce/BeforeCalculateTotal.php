<?php

namespace Growfund\Hooks\Actions\Woocommerce;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\HookNames;
use Growfund\Constants\HookTypes;
use Growfund\Hooks\BaseHook;
use Growfund\Supports\Woocommerce;

class BeforeCalculateTotal extends BaseHook
{
    public function get_name()
    {
        return HookNames::WC_BEFORE_CALCULATE_TOTAL;
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

        $cart = $args[0];

        foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
            $contribution_info_dto = Woocommerce::get_custom_cart_info_from_session();

            if (empty($contribution_info_dto)) {
                continue;
            }

            $amount = isset($contribution_info_dto->contribution_amount) ? floatval($contribution_info_dto->contribution_amount) : 0;

            $cart_item['data']->set_price($amount);
        }

        if (!growfund_app()->is_donation_mode()) {
            $bonus_support_amount = $contribution_info_dto->bonus_support_amount ?? 0;

            $cart->add_fee(__('Bonus Support Amount', 'growfund'), $bonus_support_amount, true);
        }
    }
}
