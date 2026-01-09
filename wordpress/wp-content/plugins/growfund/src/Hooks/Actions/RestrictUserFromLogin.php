<?php

namespace Growfund\Hooks\Actions;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\HookNames;
use Growfund\Constants\HookTypes;
use Growfund\Hooks\BaseHook;
use Growfund\Supports\User as UserSupport;
use WP_Error;

class RestrictUserFromLogin extends BaseHook
{
    public function get_name()
    {
        return HookNames::WP_AUTHENTICATE_USER;
    }

    public function get_type()
    {
        return HookTypes::FILTER;
    }

    public function handle(...$args)
    {
        list($user) = $args;

        if (UserSupport::is_admin($user)) {
            return $user;
        }

        if (UserSupport::is_soft_deleted($user->ID) || UserSupport::is_anonymized($user->ID)) {
            return new WP_Error(
                'user_not_found',
                __('User not found.', 'growfund')
            );
        }

        return $user;
    }
}
