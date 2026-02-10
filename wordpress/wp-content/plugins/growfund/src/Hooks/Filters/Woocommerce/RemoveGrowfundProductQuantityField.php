<?php

namespace Growfund\Hooks\Filters\Woocommerce;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\HookNames;
use Growfund\Constants\HookTypes;
use Growfund\Hooks\BaseHook;
use Growfund\Supports\Woocommerce;

class RemoveGrowfundProductQuantityField extends BaseHook
{
    public function get_name()
    {
        return HookNames::WC_SOLD_INDIVIDUALLY;
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
        list($is_sold_individually, $product) = $args;

        if (Woocommerce::is_growfund_product($product)) {
            return true;
        }
        
        return $is_sold_individually;
    }
}
