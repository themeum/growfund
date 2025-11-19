<?php

namespace Growfund\Hooks\Filters\Woocommerce;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\HookNames;
use Growfund\Constants\HookTypes;
use Growfund\Hooks\BaseHook;
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

        $contribution_info_dto = Woocommerce::get_custom_cart_info_from_session();

        if (growfund_is_wc_checkout() && !empty($contribution_info_dto)) {
            return $contribution_info_dto->campaign_name ?? $name;
        }

        return $name;
    }
}
