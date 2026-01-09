<?php

namespace Growfund\Services\Site;

defined( 'ABSPATH' ) || exit;

use Growfund\DTO\Site\Auth\LoginDTO;
use Growfund\DTO\Site\Auth\RegisterDTO;
use Growfund\DTO\Site\Auth\ResetPasswordDTO;
use Growfund\Exceptions\ValidationException;
use Growfund\Supports\User;
use Growfund\Supports\UserMeta;
use Growfund\Mails\PasswordResetLinkMail;
use Growfund\Supports\Arr;

/**
 * Authentication Service for Site
 * 
 * Handles user login, registration, and authentication using WordPress built-in functions
 * with proper security implementations.
 * 
 * @since 1.0.0
 */
class AuthService
{
    /**
     * Authenticate user login
     * 
     * @param LoginDTO $login_dto
     * @return array
     * @throws ValidationException
     */
    public function login(LoginDTO $login_dto): string
    {
        $user_login = $login_dto->user_login;
        $password = $login_dto->password;

        $authenticated_user = wp_authenticate($user_login, $password);

        if (is_wp_error($authenticated_user)) {
            if (!empty($authenticated_user->errors)) {
                $errors = Arr::make($authenticated_user->errors);
                $error = $errors->find(function($value, $key) {
                    return $key === 'user_not_found' || $key === 'fundraiser_not_active' || $key === 'email_not_verified';
                });

                if ($error) {
                    if (is_array($error)) {
                        throw ValidationException::with_errors(['credentials' => esc_html($error[0])]);
                    }

                    if (is_string($error)) {
                        throw ValidationException::with_errors(['credentials' => esc_html($error)]);
                    }
                }
            }

            throw ValidationException::with_errors(['credentials' => esc_html__('Invalid email or password.', 'growfund')]);
        }

        wp_set_auth_cookie($authenticated_user->ID, false);

        UserMeta::update($authenticated_user->ID, 'last_login', current_time('mysql', 1));

        $redirect_url = $this->get_role_based_redirect_url($authenticated_user->ID);

        return $redirect_url;
    }

    /**
     * Register a new user
     * 
     * @param RegisterDTO $register_dto
     * @return bool
     * @throws ValidationException
     */
    public function register(RegisterDTO $register_dto): bool
    {
        $existing_user = get_user_by('login', $register_dto->username);

        $is_soft_deleted = User::is_soft_deleted($existing_user->ID ?? null);

        if ($existing_user && !$is_soft_deleted) {
            throw ValidationException::with_errors(['username' => [esc_html__('Username already exists', 'growfund')]], esc_html__('Username already exists', 'growfund'));
        }

        if (!$existing_user) {
            $existing_user = get_user_by('email', $register_dto->email);

            $is_soft_deleted = User::is_soft_deleted($existing_user->ID ?? null);

            if ($existing_user && !$is_soft_deleted) {
                throw ValidationException::with_errors(['email' => [esc_html__('Email already exists', 'growfund')]], esc_html__('Email already exists', 'growfund'));
            }
        }

        if ($existing_user && $is_soft_deleted) {
            UserMeta::delete($existing_user->ID, User::SOFT_DELETE_KEY);

            return true;
        }

        $userdata = [
            'user_login' => $register_dto->username,
            'user_email' => $register_dto->email,
            'user_pass' => $register_dto->password,
            'first_name' => $register_dto->first_name,
            'last_name' => $register_dto->last_name,
            'display_name' => $register_dto->first_name . ' ' . $register_dto->last_name,
            'role' => $register_dto->role,
        ];

        $user_id = wp_insert_user($userdata);

        if (is_wp_error($user_id)) {
            throw ValidationException::with_errors(['system' => esc_html($user_id->get_error_message())]);
        }

        $meta_input = [
            'created_at' => current_time('mysql', 1)
        ];

        UserMeta::update_many($user_id, $meta_input);

        return true;
    }

    /**
     * Logout current user
     * 
     * @return bool
     */
    public function logout()
    {
        if (!growfund_user()->is_logged_in()) {
            return false;
        }

        growfund_logout();
        return true;
    }

    /**
     * Get role-based redirect URL for user after login
     * 
     * @param int $user_id
     * @param string|null $custom_redirect
     * @return string
     */
    public function get_role_based_redirect_url(int $user_id, string $custom_redirect = '')
    {
        if (!empty($custom_redirect)) {
            return $custom_redirect;
        }

        return growfund_user_dashboard_url($user_id);
    }

    /**
     * Send password reset email
     * 
     * @param array $data
     * @return bool
     * @throws ValidationException
     */
    public function send_password_reset_email(array $data)
    {
        $email = $data['email'];

        $user = get_user_by('login', $email);

        if (!$user) {
            return true;
        }

        if (User::is_soft_deleted($user->ID)) {
            return true;
        }

        UserMeta::delete($user->ID, 'password_reset_key');
        UserMeta::delete($user->ID, 'password_reset_key_consumed');
        UserMeta::delete($user->ID, 'password_reset_key_viewed');

        $reset_key = get_password_reset_key($user);

        if (is_wp_error($reset_key)) {
            throw ValidationException::with_errors(['system' => esc_html__('Unable to generate password reset key. Please try again.', 'growfund')]);
        }

        UserMeta::update($user->ID, 'password_reset_key', $reset_key);

        $mail = growfund_email(PasswordResetLinkMail::class);
        $is_sent = $mail->with(['user_id' => $user->ID])->send();

        if (!$is_sent) {
            throw ValidationException::with_errors(['system' => esc_html__('Failed to send password reset email. Please try again.', 'growfund')]);
        }

        return true;
    }

    /**
     * Check if reset key is valid
     * 
     * @param int $user_id
     * @param string $key
     * @return bool
     */
    public function is_valid_reset_key(int $user_id, string $key)
    {
        $stored_key = UserMeta::get($user_id, 'password_reset_key', true);

        if (!$stored_key) {
            return false;
        }

        if ($key !== $stored_key) {
            return false;
        }

        if ($this->is_reset_key_consumed($user_id)) {
            return false;
        }

        return true;
    }

    /**
     * Check if reset key has been consumed
     * 
     * @param int $user_id
     * @return bool
     */
    public function is_reset_key_consumed(int $user_id)
    {
        return (bool) UserMeta::get($user_id, 'password_reset_key_consumed', true);
    }

    /**
     * Check if reset key has been viewed
     * 
     * @param int $user_id
     * @return bool
     */
    public function is_reset_key_viewed(int $user_id)
    {
        return (bool) UserMeta::get($user_id, 'password_reset_key_viewed', true);
    }

    /**
     * Mark reset key as viewed
     * 
     * @param int $user_id
     * @return bool
     */
    public function mark_reset_key_viewed(int $user_id)
    {
        return UserMeta::update($user_id, 'password_reset_key_viewed', true);
    }

    /**
     * Mark reset key as consumed
     * 
     * @param int $user_id
     * @return bool
     */
    public function mark_reset_key_consumed(int $user_id)
    {
        return UserMeta::update($user_id, 'password_reset_key_consumed', true);
    }

    /**
     * Reset user password
     * 
     * @param ResetPasswordDTO $reset_password_dto
     * @return bool
     * @throws ValidationException
     */
    public function reset_password(ResetPasswordDTO $reset_password_dto): bool
    {
        $user = get_user_by('login', $reset_password_dto->login);

        if (!$user) {
            throw ValidationException::with_errors(['system' => esc_html__('Invalid user account.', 'growfund')]);
        }

        if ($this->is_reset_key_consumed($user->ID)) {
            throw ValidationException::with_errors(['system' => esc_html__('This password reset link has already been used. Please request a new one.', 'growfund')]);
        }

        if (!$this->is_valid_reset_key($user->ID, $reset_password_dto->key)) {
            throw ValidationException::with_errors(['system' => esc_html__('This password reset link is invalid. Please request a new one.', 'growfund')]);
        }

        $result = wp_set_password($reset_password_dto->password, $user->ID);

        if (is_wp_error($result)) {
            throw ValidationException::with_errors(['system' => esc_html__('Unable to update password. Please try again.', 'growfund')]);
        }

        $this->mark_reset_key_consumed($user->ID);

        UserMeta::delete($user->ID, 'password_reset_key');
        UserMeta::delete($user->ID, 'password_reset_key_consumed');
        UserMeta::delete($user->ID, 'password_reset_key_viewed');

        return true;
    }
}
