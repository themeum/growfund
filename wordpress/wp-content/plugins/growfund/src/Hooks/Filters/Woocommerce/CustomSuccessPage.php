<?php

namespace Growfund\Hooks\Filters\Woocommerce;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\HookNames;
use Growfund\Constants\HookTypes;
use Growfund\Hooks\BaseHook;
use Growfund\Services\DonationService;
use Growfund\Services\PledgeService;
use Growfund\Supports\Woocommerce;

class CustomSuccessPage extends BaseHook
{
    public function get_name()
    {
        return HookNames::WC_CHECKOUT_ORDER_RECEIVED_URL;
    }

    public function get_type()
    {
        return HookTypes::FILTER;
    }

    public function get_args_count()
    {
        return 2;
    }

    public function handle(...$args)
    {
        list($url, $order) = $args;

        foreach ($order->get_items() as $item) {
            $product_id = $item->get_product_id();

            if ($product_id !== Woocommerce::get_growfund_product_id()) {
                return $url;
            }
        }

        $contribution_service = growfund_app()->is_donation_mode()
            ? new DonationService()
            : new PledgeService();

        $contribution = $contribution_service->get_by_transaction_id($order->get_id());

        if (empty($contribution)) {
            return $url;
        }

        return growfund_url(get_the_permalink($contribution->campaign->id), [
            'uid' => $contribution->uid
        ]);
    }
}
