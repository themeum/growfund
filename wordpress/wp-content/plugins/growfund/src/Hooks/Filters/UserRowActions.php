<?php

namespace Growfund\Hooks\Filters;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\HookNames;
use Growfund\Constants\HookTypes;
use Growfund\Constants\UserCustomRowAction;
use Growfund\Hooks\BaseHook;
use Growfund\Supports\User as SupportUser;
use Growfund\View;

class UserRowActions extends BaseHook
{
    public function get_name()
    {
        return HookNames::WP_USER_ROW_ACTIONS;
    }

    public function get_type()
    {
        return HookTypes::FILTER;
    }

    public function get_args_count()
    {
        return 2;
    }

    public function handle(...$args)
    {
        $actions = $args[0];
        /** @var \WP_User */
        $user = $args[1];

        if (!current_user_can('edit_user', $user->ID)) {
            return $actions;
        }

        if (!growfund_app()->is_donation_mode() && !SupportUser::is_backer($user)) {
            $url = wp_nonce_url(
                admin_url('users.php?action=' . UserCustomRowAction::GROWFUND_MAKE_BACKER . '&user_id=' . $user->ID),
                sprintf('%s_%s', UserCustomRowAction::GROWFUND_MAKE_BACKER, $user->ID)
            );
            $actions[UserCustomRowAction::GROWFUND_MAKE_BACKER] = View::safe_html(
                sprintf(
                    '<a href="%1$s" aria-label="%2$s">%2$s</a>',
                    $url,
                    __('Make Backer', 'growfund')
                )
            );
        }

        if (growfund_app()->is_donation_mode() && !SupportUser::is_donor($user)) {
            $url = wp_nonce_url(
                admin_url('users.php?action=' . UserCustomRowAction::GROWFUND_MAKE_DONOR . '&user_id=' . $user->ID),
                sprintf('%s_%s', UserCustomRowAction::GROWFUND_MAKE_DONOR, $user->ID)
            );
            $actions[UserCustomRowAction::GROWFUND_MAKE_DONOR] = View::safe_html(
                sprintf(
                    '<a href="%1$s" aria-label="%2$s">%2$s</a>',
                    $url,
                    __('Make Donor', 'growfund')
                )
            );
        }

        if (growfund_app()->has_growfund_pro() && !SupportUser::is_fundraiser($user)) {
            $url = wp_nonce_url(
                admin_url('users.php?action=' . UserCustomRowAction::GROWFUND_MAKE_FUNDRAISER . '&user_id=' . $user->ID),
                sprintf('%s_%s', UserCustomRowAction::GROWFUND_MAKE_FUNDRAISER, $user->ID)
            );
            $actions[UserCustomRowAction::GROWFUND_MAKE_FUNDRAISER] = View::safe_html(
                sprintf(
                    '<a href="%1$s" aria-label="%2$s">%2$s</a>',
                    $url,
                    __('Make Fundraiser', 'growfund')
                )
            );
        }


        return $actions;
    }
}
