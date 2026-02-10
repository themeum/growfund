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

        if (!Woocommerce::has_growfund_product_in_order($order)) {
            return $url;      
        }

        if (growfund_app()->is_donation_mode()) {
            $donation_service = new DonationService();
            $donation = $donation_service->get_by_id(Woocommerce::get_contribution_id_from_order($order));

            growfund_flash_set_message('contribution_confirmed', ['uid' => $donation->uid]);

            return growfund_url(growfund_campaign_url($donation->campaign->slug), [
				'uid' => $donation->uid
			]);
        }

        $pledge_service = new PledgeService();
        $pledge = $pledge_service->get_by_id(Woocommerce::get_contribution_id_from_order($order));

        growfund_flash_set_message('contribution_confirmed', ['uid' => $pledge->uid]);

        return growfund_url(growfund_campaign_url($pledge->campaign->slug), [
            'uid' => $pledge->uid
        ]);
    }
}
