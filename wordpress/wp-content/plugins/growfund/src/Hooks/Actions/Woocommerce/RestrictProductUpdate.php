<?php

namespace Growfund\Hooks\Actions\Woocommerce;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\HookNames;
use Growfund\Constants\HookTypes;
use Growfund\Hooks\BaseHook;
use Growfund\Supports\Woocommerce;

class RestrictProductUpdate extends BaseHook
{
    public function get_name()
    {
        return HookNames::WC_BEFORE_PRODUCT_SAVE;
    }

    public function get_type()
    {
        return HookTypes::ACTION;
    }

    public function handle(...$args)
    {
        $product = $args[0];

        if (Woocommerce::is_growfund_product($product)) {
            wp_die(esc_html__('This product cannot be updated as it is required by Growfund.', 'growfund'));
        }
    }
}
