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

        if (growfund_user($user->ID)->is_fundraiser()) {
            if (!growfund_app_features()->is_pro() || growfund_user($user->ID)->get_meta(Fundraiser::STATUS) !== FundraiserStatus::ACTIVE) {
                return new WP_Error(
                'fundraiser_not_active',
                __('Your account is not approved yet.', 'growfund')
				);
            }
            
        }

        if (growfund_settings(AppSettings::SECURITY)->is_enabled_email_verification() && !growfund_user($user->ID)->is_verified()) {
            return new WP_Error(
                'email_not_verified',
                __('You must verify your email before logging in.', 'growfund')
            );
        }


        return $user;
    }
}
