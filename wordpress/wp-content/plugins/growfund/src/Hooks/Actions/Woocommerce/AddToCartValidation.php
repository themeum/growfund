<?php

namespace Growfund\Hooks\Actions\Woocommerce;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\HookNames;
use Growfund\Constants\HookTypes;
use Growfund\Hooks\BaseHook;
use Growfund\Supports\Woocommerce;

class AddToCartValidation extends BaseHook
{
    public function get_name()
    {
        return HookNames::WC_ADD_TO_CART_VALIDATION;
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
        list($passed, $product_id) = $args;

        if (Woocommerce::is_growfund_product($product_id)) {
			if ($passed) {
                Woocommerce::empty_cart();
            }

            return $passed;
		}

        if (Woocommerce::has_growfund_product_in_cart()) {
            Woocommerce::empty_cart();
        }

        return $passed;
    }
}
