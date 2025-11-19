<?php

namespace Growfund\Hooks\Filters;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\HookNames;
use Growfund\Constants\HookTypes;
use Growfund\Hooks\BaseHook;

class LoginRedirect extends BaseHook
{
    public function get_name()
    {
        return HookNames::LOGIN_REDIRECT;
    }

    public function get_type()
    {
        return HookTypes::FILTER;
    }

    public function get_args_count()
    {
        return 3;
    }

    public function handle(...$args)
    {
        list($redirect_to, $request, $user) = $args;

        if (empty($user->ID)) {
            return $redirect_to;
        }

        return growfund_user_dashboard_url($user->ID);
    }
}
