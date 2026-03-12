<?php

namespace Growfund\Hooks\Actions\Woocommerce;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\HookNames;
use Growfund\Constants\HookTypes;
use Growfund\Constants\OptionKeys;
use Growfund\Hooks\BaseHook;
use Growfund\Supports\Option;
use Growfund\Supports\Woocommerce;

class RestrictProductDeletion extends BaseHook
{
    public function get_name()
    {
        return HookNames::WP_TRASH_POST;
    }

    public function get_type()
    {
        return HookTypes::ACTION;
    }

    public function handle(...$args)
    {
        $product_id = $args[0];

        if (
            Woocommerce::is_active() 
            && Woocommerce::get_growfund_product_id() > 0 
            && Woocommerce::is_growfund_product((int) $product_id)
        ) {
            wp_die(esc_html__('This product cannot be deleted as it is required by Growfund.', 'growfund'));
        }

        if ((int) $product_id === growfund_wc_product_id()) {
            Option::delete(OptionKeys::WC_PRODUCT_ID);
        }
    }
}
