<?php

namespace Growfund\Hooks\Actions\Woocommerce;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\HookNames;
use Growfund\Constants\HookTypes;
use Growfund\Hooks\BaseHook;
use Growfund\Supports\Woocommerce;

class HideProductPermalinkEdit extends BaseHook
{
    public function get_name()
    {
        return HookNames::WP_GET_SAMPLE_PERMALINK_HTML;
    }

    public function get_type()
    {
        return HookTypes::ACTION;
    }

    public function get_args_count()
    {
        return 5;
    }

    public function handle(...$args)
    {
        $return = $args[0];
        $id = $args[1];

        if ($id === Woocommerce::get_growfund_product_id()) {
            return '';
        }

        return $return;
    }
}
