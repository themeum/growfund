<?php

namespace Growfund\Supports;

use Growfund\Constants\Status\FundraiserStatus;
use Growfund\Constants\UserTypes\Admin;
use Growfund\Constants\UserTypes\Backer;
use Growfund\Constants\UserTypes\Collaborator;
use Growfund\Constants\UserTypes\Donor;
use Growfund\Constants\UserTypes\Fundraiser;
use Growfund\DTO\AddressDTO;
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
    public static function is_soft_deleted($user_id = null)
    {
        if (is_null($user_id)) {
            return false;
        }

        return ((bool) UserMeta::get($user_id, static::SOFT_DELETE_KEY)) === true;
    }

    /**
     * Check if a user is anonymized.
     * @since 1.0.3
     * @param int|null $user_id The ID of the user.
     * 
     * @return bool
     */
    public static function is_anonymized($user_id = null) {
        if (is_null($user_id)) {
            return false;
        }

        return ((bool) UserMeta::get($user_id, static::IS_ANONYMIZED)) === true;
    }

    public static function verify_email($user_id, $token)
    {
        $actual_token = UserMeta::get($user_id, 'email_verification_token');
        $created_at    = intval(UserMeta::get($user_id, 'email_verification_created', true));

        $expired = (time() - $created_at) > DAY_IN_SECONDS; // DAY_IN_SECONDS is 86 400

        if (!$expired && $actual_token === $token) {
            static::mark_as_verified($user_id);
            growfund_flash_set_message('success', __('Your email has been verified', 'growfund'));
            growfund_redirect(growfund_login_url());
        }

        growfund_flash_set_message('error', __('Your email could not be verified', 'growfund'));
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
     * Check if the user is a collaborator of a campaign
     *
     * @param WP_User|null $user The user object.
     * @return bool
     */
    public static function is_collaborator($user)
    {
        return static::has_role($user, Collaborator::ROLE);
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
     * Get the current user role
     * @param WP_User|null $user The user object.
     *
     * @return string|null
     */
    public static function get_active_role($user)
    {
        if (static::is_admin($user)) {
            return Admin::ROLE;
        }

        if (static::is_fundraiser($user)) {
            return Fundraiser::ROLE;
        }

        if (static::is_collaborator($user)) {
            return Collaborator::ROLE;
        }

        if (static::is_donor($user)) {
            return Donor::ROLE;
        }

        if (static::is_backer($user)) {
            return Backer::ROLE;
        }

        return null;
    }

    /**
     * Check if the current user has a specific role
     * @param WP_User|null $user The user object.
     * @param string $role
     * @return bool
     */
    public function has_active_role($user, string $role)
    {
        return static::get_active_role($user) === $role;
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

    /**
     * Get the joined date of a user.
     * @since 1.0.3
     * @param WP_User $user
     * 
     * @return string|null
     */
    public static function get_joined_date($user) {
        if (static::is_fundraiser($user)) {
            $status = static::get_status($user->ID);

            if ($status !== FundraiserStatus::ACTIVE) {
                return null;
            }

            $joined_at = UserMeta::get($user->ID, 'joined_at');

            return !empty($joined_at) ? $joined_at : $user->user_registered;
        }

        return $user->user_registered;
    }

    /**
     * Get the status of a user.
     * @since 1.0.3
     * @param int $user_id
     * 
     * @return string|null
     */
    public static function get_status($user_id) {
        $status = UserMeta::get($user_id, 'status');
        return !empty($status) ? $status : null;
    }

    /**
     * Get the created_by id of a user.
     * @since 1.0.3
     * @param int $user_id
     * 
     * @return string|null
     */
    public static function get_created_by($user_id) {
        $created_by = UserMeta::get($user_id, 'created_by');

        return !empty($created_by) ? $created_by : null;
    }

    /**
     * Get the avatar image of a user.
     * @since 1.0.3
     * @param int $user_id
     * 
     * @return array|null
     */
    public static function get_avatar_image($user_id) {
        $image = UserMeta::get($user_id, 'image');

        return !empty($image) ? MediaAttachment::make((int) $image) : null;
    }

    /**
     * Get the phone number of a user.
     * @since 1.0.3
     * @param int $user_id
     * 
     * @return string|null
     */
    public static function get_phone_number($user_id) {
        $phone = UserMeta::get($user_id, 'phone');

        return !empty($phone) ? $phone : null;
    }

    /**
     * Check if the billing address is the same as the shipping address.
     * 
     * @param int $user_id
     * 
     * @return bool
     */
    public static function is_billing_address_same(int $user_id) {
        if (growfund_app()->is_donation_mode()) {
            return false;
        }

        return boolval(UserMeta::get($user_id, 'is_billing_address_same') ?? false);
    }

    /**
     * Get the billing address of a user.
     * 
     * @param int $user_id
     * 
     * @return AddressDTO|null
     */
    public static function get_billing_address(int $user_id) {
        if (static::is_billing_address_same($user_id)) {
            return static::get_shipping_address($user_id);
        }

        $billing_address = UserMeta::get($user_id, 'billing_address');
        $billing_address = !empty($billing_address) ? maybe_unserialize($billing_address) : null;

        return !empty($billing_address) ? AddressDTO::from_array($billing_address ?? []) : null;
    }

    /**
     * Get the shipping address of a user.
     * 
     * @param int $user_id
     * 
     * @return AddressDTO|null
     */
    public static function get_shipping_address(int $user_id) {
        $shipping_address = UserMeta::get($user_id, 'shipping_address');
        $shipping_address = !empty($shipping_address) ? maybe_unserialize($shipping_address) : null;

        return !empty($shipping_address) ? AddressDTO::from_array($shipping_address ?? []) : null;
    }
    
    /*
     * Register a custom role.
     * @since 1.1.0
     * 
     * @param string $role_name
     * @param string $role_title
     * 
     * @return void
     */
    public static function register_role(string $role_name, string $role_title) {
        add_role($role_name, $role_title, ['read' => true]);

        $capabilities = growfund_get_all_capabilities($role_name);

        foreach ($capabilities as $capability) {
            $role_object = get_role($role_name);

            if ($role_object && !$role_object->has_cap($capability)) {
                $role_object->add_cap($capability);
            }
        }
    }
}
