<?php

namespace Growfund\Supports;

use Growfund\Constants\UserTypes\Admin;
use Growfund\Constants\UserTypes\Backer;
use Growfund\Constants\UserTypes\Donor;
use Growfund\Constants\UserTypes\Fundraiser;
use WP_User;

defined( 'ABSPATH' ) || exit;

class User
{
    const SOFT_DELETE_KEY = 'is_soft_deleted';
    const IS_ANONYMIZED = 'is_anonymized';
    const IS_GUEST = 'is_guest';

    /**
     * Check if a user is soft deleted.
     *
     * @param int|null $user_id The ID of the user.
     *
     * @return bool True if the user is soft deleted, false otherwise.
     */
    public static function is_soft_deleted_user($user_id = null)
    {
        if (is_null($user_id)) {
            return false;
        }

        return ((bool) UserMeta::get($user_id, static::SOFT_DELETE_KEY)) === true;
    }

    public static function verify_email($user_id, $token)
    {
        $actual_token = UserMeta::get($user_id, 'email_verification_token');
        $created_at    = intval(UserMeta::get($user_id, 'email_verification_created', true));

        $expired = (time() - $created_at) > DAY_IN_SECONDS; // DAY_IN_SECONDS is 86 400

        if (!$expired && $actual_token === $token) {
            static::mark_as_verified($user_id);
            FlashMessage::set('success', __('Your email has been verified', 'growfund'));
            growfund_redirect(growfund_login_url());
        }

        FlashMessage::set('error', __('Your email could not be verified', 'growfund'));
        growfund_redirect(growfund_login_url());
    }

    public static function generate_verification_token($user_id)
    {
        $token = wp_generate_password(32, false);

        UserMeta::update($user_id, 'email_verified', 0);
        UserMeta::update($user_id, 'email_verification_token', $token);
        UserMeta::update($user_id, 'email_verification_created', time());

        return $token;
    }

    /**
     * Get the user roles
     *
     * @param WP_User|null $user The user object.
     * @return array
     */
    public static function get_roles($user)
    {
        return $user->roles ?? [];
    }

    /**
     * Check if the user has a specific role
     *
     * @param WP_User|null $user The user object.
     * @param string $role
     * @return bool
     */
    public static function has_role($user, string $role)
    {
        return in_array($role, static::get_roles($user), true);
    }

    /**
     * Check if the user is an admin
     *
     * @param WP_User|null $user The user object.
     * @return bool
     */
    public static function is_admin($user)
    {
        return static::has_role($user, Admin::ROLE);
    }

    /**
     * Check if the user is a fundraiser
     *
     * @param WP_User|null $user The user object.
     * @return bool
     */
    public static function is_fundraiser($user)
    {
        return static::has_role($user, Fundraiser::ROLE);
    }

    /**
     * Check if the current user is a backer
     *
     * @param WP_User|null $user The user object.
     * @return bool
     */
    public static function is_backer($user)
    {
        return static::has_role($user, Backer::ROLE);
    }

    /**
     * Check if the current user is a donor
     *
     * @param WP_User|null $user The user object.
     * @return bool
     */
    public static function is_donor($user)
    {
        return static::has_role($user, Donor::ROLE);
    }

    /**
     * Check if the current user is a donor
     *
     * @param WP_User|null $user The user object.
     * @return bool
     */
    public static function is_verified($user)
    {
        if (empty($user)) {
            return false;
        }
        
        return static::is_admin($user) || filter_var(
            UserMeta::get($user->ID, 'email_verified'),
            FILTER_VALIDATE_BOOLEAN
        );
    }

    public static function mark_as_verified($user_id)
    {
        UserMeta::update($user_id, 'email_verified', 1);
        UserMeta::delete($user_id, 'email_verification_token');
        UserMeta::delete($user_id, 'email_verification_created');

        return true;
    }

    /**
     * Mark a user as guest.
     *
     * @param int $user_id The ID of the user.
     *
     * @return bool True if the user is marked as guest, false otherwise.
     */
    public static function mark_as_guest($user_id)
    {
        UserMeta::update($user_id, static::IS_GUEST, true);

        return true;
    }

    /**
     * Check if a user is marked as guest.
     *
     * @param int $user_id The ID of the user.
     *
     * @return bool True if the user is marked as guest, false otherwise.
     */
    public static function is_guest($user_id)
    {
        return filter_var(
            UserMeta::get($user_id, static::IS_GUEST),
            FILTER_VALIDATE_BOOLEAN
        );
    }
}
