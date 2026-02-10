<?php

namespace Growfund\Hooks\Filters\Woocommerce;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\HookNames;
use Growfund\Constants\HookTypes;
use Growfund\Hooks\BaseHook;
use Growfund\Services\DonationService;
use Growfund\Services\PledgeService;
use Growfund\Supports\Woocommerce;

class CheckoutItemName extends BaseHook
{
    public function get_name()
    {
        return HookNames::WC_PRODUCT_GET_NAME;
    }

    public function get_type()
    {
        return HookTypes::FILTER;
    }

    public function get_args()
    {
        return 2;
    }

    public function handle(...$args)
    {
        list($name) = $args;

        if (!growfund_is_wc_checkout()) {
            return $name;
        }

        $contribution_id = Woocommerce::get_contribution_id_from_cart();

        if (!empty($contribution_id)) {
            if (growfund_app()->is_donation_mode()) {
                $donation_service = new DonationService();
                $donation = $donation_service->get_by_id($contribution_id);

				return $donation->campaign->title ?? $name;
            }

            $pledge_service = new PledgeService();
            $pledge = $pledge_service->get_by_id($contribution_id);
            return $pledge->campaign->title ?? $name;
        }

        return $name;
    }
}
