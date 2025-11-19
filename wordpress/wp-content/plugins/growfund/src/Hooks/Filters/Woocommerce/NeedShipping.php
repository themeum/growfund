<?php

namespace Growfund\Hooks\Filters\Woocommerce;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\HookNames;
use Growfund\Constants\HookTypes;
use Growfund\Hooks\BaseHook;
use Growfund\Supports\Woocommerce;

class NeedShipping extends BaseHook
{
    public function get_name()
    {
        return HookNames::WC_CART_NEEDS_SHIPPING;
    }

    public function get_type()
    {
        return HookTypes::FILTER;
    }

    public function handle(...$args)
    {
        $contribution_info_dto = Woocommerce::get_custom_cart_info_from_session();

        if (empty($contribution_info_dto->reward_id)) {
            return false;
        }

        return true;
    }
}
