<?php

namespace Growfund\Supports;

defined( 'ABSPATH' ) || exit;

use Growfund\Core\AppSettings;

class Auth
{
    public static function login_url(string $redirect = '')
    {
        $login_url = home_url('/auth/login/');
        
        if (!empty($redirect)) {
            $login_url = add_query_arg('redirect_to', urlencode($redirect), $login_url);
        }

        $settings = growfund_settings(AppSettings::GENERAL);

        if (!empty($settings->get('login_page'))) {
            $login_url = get_permalink($settings->get('login_page'));
        }

        return $login_url;
    }

    public static function register_url($is_fundraiser = false)
    {
        $contributor_register_page = home_url('/auth/register/');
        $fundraiser_register_page = home_url('/auth/register-fundraiser/');

        $settings = growfund_settings(AppSettings::GENERAL);

        if (!empty($settings->get('registration_page'))) {
            $contributor_register_page = get_permalink($settings->get('registration_page'));
        }

        return $is_fundraiser ? $fundraiser_register_page : $contributor_register_page;
    }

    public static function forget_password_url()
    {
        return home_url('/auth/forgot-password/');
    }

    public static function reset_password_url()
    {
        return home_url('/auth/reset-password/');
    }
}
