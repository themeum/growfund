<?php

namespace Growfund\Hooks\Filters;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\HookNames;
use Growfund\Constants\HookTypes;
use Growfund\Hooks\BaseHook;

class HideAdminBar extends BaseHook
{
    public function get_name()
    {
        return HookNames::SHOW_ADMIN_BAR;
    }

    public function get_type()
    {
        return HookTypes::FILTER;
    }

    public function handle(...$args)
    {
        if (growfund_is_react_site()) {
            return false;
        }

        // @todo: will uncomment when we set a menu to go to the dashboard for growfund user

        // if (
        //     growfund_user()->is_fundraiser() 
        //     || growfund_user()->is_collaborator() 
        //     || growfund_user()->is_backer() 
        //     || growfund_user()->is_donor()
        // ) {
        //     return false;
        // }

        return $args[0];
    }
}
