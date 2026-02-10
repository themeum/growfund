<?php

namespace Growfund\Hooks\Actions\Woocommerce;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\HookNames;
use Growfund\Constants\HookTypes;
use Growfund\Hooks\BaseHook;
use Growfund\Services\DonationService;
use Growfund\Services\PledgeService;
use Growfund\Supports\Woocommerce;

class NewOrderStoreAPI extends BaseHook
{
    public function get_name()
    {
        return HookNames::WC_STORE_API_CHECKOUT_ORDER_PROCESSED;
    }

    public function get_type()
    {
        return HookTypes::ACTION;
    }

    public function handle(...$args)
    {
        if (!Woocommerce::has_growfund_product_in_cart()) {
            return;
        }

        
        $order_id = $args[0];

        $order = wc_get_order($order_id);

        if (!Woocommerce::has_growfund_product_in_order($order)) {
            return;
        }

        $contribution_id = Woocommerce::get_contribution_id_from_order($order);

        if (empty($contribution_id)) {
            return;
        }

        if (growfund_app()->is_donation_mode()) {
            $donation_service = new DonationService();
            $donation_service->partial_update($contribution_id, [
                'transaction_id' => 'wc_' . $order->get_id(),
                'payment_method' => wp_json_encode(Woocommerce::get_payment_method_from_order($order)->to_array()),
            ]);

            return;
        }

        $pledge_service = new PledgeService();
        $pledge_service->partial_update($contribution_id, [
			'transaction_id' => 'wc_' . $order->get_id(),
			'payment_method' => wp_json_encode(Woocommerce::get_payment_method_from_order($order)->to_array()),
		]);
    }
}
