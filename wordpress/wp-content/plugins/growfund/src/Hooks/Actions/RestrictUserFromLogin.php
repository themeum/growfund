<?php

namespace Growfund\Hooks\Actions;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\HookNames;
use Growfund\Constants\HookTypes;
use Growfund\Constants\MetaKeys\Fundraiser;
use Growfund\Constants\Status\FundraiserStatus;
use Growfund\Core\AppSettings;
use Growfund\Hooks\BaseHook;
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

        if (growfund_user($user->ID)->is_admin()) {
            return $user;
        }

        if (growfund_user($user->ID)->is_soft_deleted() || growfund_user($user->ID)->is_anonymized()) {
            return new WP_Error(
                'user_not_found',
                __('User not found.', 'growfund')
            );
        }

        return $user;
    }
}
