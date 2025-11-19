<?php

namespace Growfund\Supports;

defined( 'ABSPATH' ) || exit;

class AdminUser
{

    /**
     * Get admin user
     * 
     * @return \WP_User|null
     */
    public static function get()
    {
        $admin = get_user_by('email', static::get_email());

        if (!$admin) {
            return null;
        }

        return $admin;
    }

    /**
     * Get admin user id
     * 
     * @return int
     */
    public static function get_id()
    {
        return static::get()->ID ?? 0;
    }

    public static function get_email()
    {
        return get_option('admin_email');
    }
}
