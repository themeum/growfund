<?php

namespace Growfund\Supports;

defined( 'ABSPATH' ) || exit;

use Growfund\Core\AppSettings;

class Auth
{
    public static function login_url(string $redirect = '')
    {
        $login_url = home_url('/auth/login/');

        $page_id = growfund_settings(AppSettings::PAGES)->get_login_page_id();

        if (!empty($page_id)) {
            $login_url = get_permalink($page_id);
        }

        if (!empty($redirect)) {
            $login_url = add_query_arg('redirect_to', urlencode($redirect), $login_url);
        }

        return $login_url;
    }

    public static function register_url($is_fundraiser = false)
    {
        if ($is_fundraiser) {
			$fundraiser_register_page = home_url('/auth/register-fundraiser/');
            $page_id = growfund_settings(AppSettings::PAGES)->get_fundraiser_registration_page_id();

            if (!empty($page_id)) {
                $fundraiser_register_page = get_permalink($page_id);
            }

            return $fundraiser_register_page;
        }

        $contributor_register_page = home_url('/auth/register/');
        $page_id = growfund_settings(AppSettings::PAGES)->get_registration_page_id();

        if (!empty($page_id)) {
            $contributor_register_page = get_permalink($page_id);
        }

        return $contributor_register_page;
    }

    public static function forget_password_url()
    {
        return home_url('/auth/forgot-password/');
    }

    public static function reset_password_url()
    {
        return home_url('/auth/reset-password/');
    }

	public static function reset_link_url()
    {
        return home_url('auth/reset-password-link');
    }
}
