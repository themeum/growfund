<?php

namespace Growfund\Hooks\Filters;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\HookNames;
use Growfund\Constants\HookTypes;
use Growfund\Hooks\BaseHook;

class BodyClass extends BaseHook
{
    public function get_name()
    {
        return HookNames::BODY_CLASS;
    }

    public function get_type()
    {
        return HookTypes::FILTER;
    }

    public function handle(...$args)
    {
        $classes = $args[0];

        if (growfund_is_react_site() || is_admin()) {
            return array_merge($classes, ['toplevel_page_growfund']);
        }

        return $args[0];
    }
}
