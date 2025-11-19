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

        if ($product_id === Woocommerce::get_growfund_product_id()) {
            if ($passed) {
                WC()->cart->empty_cart();
            }

            return $passed;
        }

        $this->remove_growfund_product_from_cart();

        return $passed;
    }

    protected function remove_growfund_product_from_cart()
    {
        foreach (WC()->cart->get_cart() as $key => $cart_item) {
            if ((int) $cart_item['product_id'] === (int) Woocommerce::get_growfund_product_id()) {
                WC()->cart->remove_cart_item($key);
            }
        }

        Woocommerce::unset_custom_cart_info_from_session();
    }
}
