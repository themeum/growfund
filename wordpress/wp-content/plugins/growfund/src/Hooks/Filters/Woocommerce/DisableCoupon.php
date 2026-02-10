<?php

namespace Growfund\Hooks\Filters\Woocommerce;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\HookNames;
use Growfund\Constants\HookTypes;
use Growfund\Hooks\BaseHook;
use Growfund\Supports\Woocommerce;

class DisableCoupon extends BaseHook
{
    public function get_name()
    {
        return HookNames::WC_COUPONS_ENABLE;
    }

    public function get_type()
    {
        return HookTypes::FILTER;
    }

    public function handle(...$args)
    {
        $enabled = $args[0];
        
        if (!WC()->cart) {
			return $enabled;
		}

        if (Woocommerce::has_growfund_product_in_cart()) {
            return false;
        }

		return $enabled;
    }
}
