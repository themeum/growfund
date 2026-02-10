<?php

namespace Growfund\Hooks\Actions\Woocommerce;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\HookNames;
use Growfund\Constants\HookTypes;
use Growfund\Hooks\BaseHook;
use Growfund\Supports\Woocommerce;

class CreateOrderLineItem extends BaseHook
{
    public function get_name()
    {
        return HookNames::WC_CHECKOUT_ORDER_LINE_ITEM;
    }

    public function get_type()
    {
        return HookTypes::ACTION;
    }

    public function get_args_count()
    {
        return 4;
    }

    public function handle(...$args)
    {
        list($item, $cart_item_key, $values, $order) = $args;

        if (!Woocommerce::is_growfund_product($item->get_product_id())) {
            return;
        }

        $contribution_id = $values[growfund_with_prefix('contribution_id')] ?? null;

        if (!empty($contribution_id)) {
            $order->update_meta_data(growfund_with_prefix('contribution_id'), $contribution_id );
            $item->add_meta_data(growfund_with_prefix('contribution_id'), $contribution_id, true);
		}
    }
}
