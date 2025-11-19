<?php

namespace Growfund\Hooks\Actions\Woocommerce;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\HookNames;
use Growfund\Constants\HookTypes;
use Growfund\Hooks\BaseHook;
use Growfund\Supports\Woocommerce;

class StoreAPIUpdateCheckoutOrder extends BaseHook
{
    public function get_name()
    {
        return HookNames::WC_STORE_API_CHECKOUT_UPDATE_ORDER_FROM_REQUEST;
    }

    public function get_type()
    {
        return HookTypes::ACTION;
    }

    public function get_args_count()
    {
        return 2;
    }

    public function handle(...$args)
    {
        if (!Woocommerce::is_native_checkout()) {
            return;
        }

        list($order, $request) = $args;

        $payload = $request->get_body() ? json_decode($request->get_body(), true) : [];

        if (growfund_app()->is_donation_mode()) {
            $this->save_donation_fields($order, $payload);
        } else {
            $this->save_pledge_fields($order, $payload);
        }
    }

    protected function save_pledge_fields($order, $payload)
    {
        $contribution_info_dto = Woocommerce::get_custom_cart_info_from_session();

        $field_id = 'growfund/bonus_support_amount';

        if (!isset($payload['additional_fields'][$field_id])) {
            return;
        }

        $bonus_support_amount = $payload['additional_fields'][$field_id];

        $order->update_meta_data("_wc_other/$field_id", $bonus_support_amount);

        $contribution_info_dto->bonus_support_amount = $bonus_support_amount;

        Woocommerce::set_custom_cart_info_to_session($contribution_info_dto);
    }

    protected function save_donation_fields($order, $payload)
    {
        $contribution_info_dto = Woocommerce::get_custom_cart_info_from_session();

        $fund_id = 'growfund/fund_id';
        $amount_field_id = 'growfund/donation_amount';
        $is_anonymous_field_id = 'growfund/is_anonymous';

        if (!empty($payload['additional_fields'][$amount_field_id])) {
            $order->update_meta_data("_wc_other/$amount_field_id", $payload['additional_fields'][$amount_field_id]);

            $contribution_info_dto->contribution_amount = $payload['additional_fields'][$amount_field_id] ?? $contribution_info_dto->contribution_amount ?? 0;

            Woocommerce::set_custom_cart_info_to_session($contribution_info_dto);
        }

        if (!empty($payload['additional_fields'][$is_anonymous_field_id])) {
            $order->update_meta_data("_wc_other/$is_anonymous_field_id", $payload['additional_fields'][$is_anonymous_field_id]);
        }

        if (!empty($payload['additional_fields'][$fund_id])) {
            $order->update_meta_data("_wc_other/$fund_id", $payload['additional_fields'][$fund_id]);
        }
    }
}
