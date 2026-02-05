<?php

namespace Growfund\Hooks\Actions;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\HookNames;
use Growfund\Constants\HookTypes;
use Growfund\Hooks\BaseHook;
use Growfund\Supports\Branding;

/**
 * This hook is responsible for enqueueing the admin script.
 *
 * @since 1.0.0
 */
class EnqueueBrandingStyles extends BaseHook
{
    public function get_name()
    {
        return HookNames::WP_HEAD;
    }

    public function get_type()
    {
        return HookTypes::ACTION;
    }

    public function get_priority()
    {
        return 99;
    }

    public function handle(...$args)
    {
        if (is_admin() || !growfund_is_react_site()) {
            return;
        }

        Branding::enqueue_branding_style_variables();
    }
}
