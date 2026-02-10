<?php

namespace Growfund\Hooks\Filters\Woocommerce;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\HookNames;
use Growfund\Constants\HookTypes;
use Growfund\Hooks\BaseHook;
use Growfund\Supports\Woocommerce;

class RemoveTaxFromGrowfundProduct extends BaseHook
{
    public function get_name()
    {
        return HookNames::WC_PRODUCT_IS_TAXABLE;
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
        list($taxable, $product) = $args;

		if (Woocommerce::is_growfund_product($product)) {
			return false;
		}

		return $taxable;
    }
}
