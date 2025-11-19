<?php

namespace Growfund\Hooks\Actions\Woocommerce;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\HookNames;
use Growfund\Constants\HookTypes;
use Growfund\Hooks\BaseHook;

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
        $post_id = $args[0];

        if ($post_id === growfund_wc_product_id()) {
            wp_die(esc_html__('This product cannot be deleted as it is required by Growfund.', 'growfund'));
        }
    }
}
