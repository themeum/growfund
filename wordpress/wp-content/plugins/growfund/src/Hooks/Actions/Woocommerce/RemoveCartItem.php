<?php

namespace Growfund\Hooks\Actions\Woocommerce;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\HookNames;
use Growfund\Constants\HookTypes;
use Growfund\Hooks\BaseHook;
use Growfund\Supports\Woocommerce;

class RemoveCartItem extends BaseHook
{
    public function get_name()
    {
        return HookNames::WC_REMOVE_CART_ITEM;
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
        list($cart_item_key, $cart) = $args;

        $removed_item = $cart->removed_cart_contents[$cart_item_key] ?? null;

        if (!$removed_item) {
            return;
        }

        $product_id = $removed_item['product_id'] ?? null;

        if (!Woocommerce::is_growfund_product($product_id)) {
            return;
        }

        $contribution_id = $removed_item[growfund_with_prefix('contribution_id')] ?? null;

        if (empty($contribution_id)) {
            return;
        }

        Woocommerce::remove_pending_contribution_from_cart((int) $contribution_id);
    }
}
