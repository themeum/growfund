<?php

namespace Growfund\Controllers\Site;

defined( 'ABSPATH' ) || exit;

use Growfund\Contracts\Request;
use Growfund\Constants\UserTypes\Backer;
use Growfund\Constants\UserTypes\Donor;
use Growfund\Constants\UserTypes\Fundraiser;

use Growfund\DTO\Site\Auth\LoginDTO;
use Growfund\DTO\Site\Auth\RegisterDTO;
use Growfund\DTO\Site\Auth\ResetPasswordDTO;
use Growfund\Services\Site\AuthService;
use Growfund\Validation\Validator;
use Growfund\Exceptions\ValidationException;
use Growfund\Sanitizer;
use Exception;

/**
 * Authentication Controller for Site
 * 
 * Handles login and registration requests with proper separation of concerns.
 * Controller only handles request/response, business logic is in AuthService.
 * 
 * @since 1.0.0
 */
class AuthController
{
    /**
     * @var AuthService
     */
    protected $auth_service;

    /**
     * Initialize controller with AuthService
     */
    public function __construct()
    {
        $this->auth_service = new AuthService();
    }

    /**
     * Show login page
     * 
     * @param Request $request
     * @return string
     */
    public function show_login()
    {
        return growfund_renderer()->get_html('site.auth.login', [
            'redirect_to' => growfund_user_dashboard_url(),
            'is_shortcode' => false,
        ]);
    }

    /**
     * Handle AJAX login
     * 
     * @param Request $request
     * @return void
     */
    public function ajax_login(Request $request)
    {
        $raw_data = [
            'user_login' => $request->get_string('user_login'),
            'password' => $request->get_string('password'),
            'redirect_to' => $request->get_url('redirect_to')
        ];

        $validator = Validator::make($raw_data, LoginDTO::validation_rules());

        if ($validator->is_failed()) {
            throw ValidationException::with_errors($validator->get_errors(), esc_html__('Login validation failed!', 'growfund')); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- validation exception intentionally ignored
        }

        $sanitized_data = Sanitizer::make($raw_data, LoginDTO::sanitization_rules())->get_sanitized_data();

        $login_dto = LoginDTO::from_array($sanitized_data);

        $redirect_url = $this->auth_service->login($login_dto);

        if (empty($redirect_url)) {
            $redirect_url = home_url();
        }

        // @todo: Need to return JSON response for AJAX requests
        growfund_redirect($redirect_url);
    }

    /**
     * Show registration page
     * 
     * @param Request $request
     * @return string
     */
    public function show_register()
    {
        return growfund_renderer()->get_html('site.auth.register', [
            'is_shortcode' => false,
            'is_fundraiser' => false,
            'redirect_to' => growfund_login_url()
        ]);
    }

    /**
     * Handle AJAX registration
     * 
     * @param Request $request
     * @return \Growfund\Http\SiteResponse
     */
    public function ajax_register(Request $request)
    {
        $is_donation_mode = growfund_app()->is_donation_mode();
        $role = $is_donation_mode ? Donor::ROLE : Backer::ROLE;

        return $this->handle_registration($request, $role);
    }

    /**
     * Handle AJAX fundraiser registration
     * 
     * @param Request $request
     * @return \Growfund\Http\SiteResponse
     */
    public function ajax_register_fundraiser(Request $request)
    {
        return $this->handle_registration($request, Fundraiser::ROLE);
    }

    /**
     * Common registration handler
     * 
     * @param Request $request
     * @param string $role
     * @return \Growfund\Http\SiteResponse
     */
    protected function handle_registration(Request $request, string $role)
    {
        $raw_data = [
            'first_name' => $request->get_string('first_name'),
            'last_name' => $request->get_string('last_name'),
            'email' => $request->get_email('email'),
            'username' => $request->get_string('username'),
            'password' => $request->get_string('password'),
            'password_confirmation' => $request->get_string('password_confirmation'),
            'role' => $role,
            'redirect_to' => $request->get_url('redirect_to')
        ];

        $validator = Validator::make($raw_data, RegisterDTO::validation_rules());

        if ($validator->is_failed()) {
            throw ValidationException::with_errors($validator->get_errors()); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- validation exception intentionally ignored
        }

        $sanitized_data = Sanitizer::make($raw_data, RegisterDTO::sanitization_rules())->get_sanitized_data();

        $register_dto = RegisterDTO::from_array($sanitized_data);

        if ($this->auth_service->register($register_dto)) {
            return growfund_site_response()->json([
                'message' => __('Registration successful. Please check your email to verify your account.', 'growfund'),
                'redirect_url' => growfund_login_url()
            ]);
        }
    }

    /**
     * Show forgot password page
     * 
     * @param Request $request
     * @return string
     */
    public function show_forgot_password(Request $request)
    {
        $submitted_email = $request->get_string('email');

        $error = growfund_flash_get_message('growfund_reset_password_error');

        return growfund_renderer()->get_html('site.auth.forgot-password', [
            'submitted_email' => $submitted_email,
            'error' => $error
        ]);
    }

    /**
     * Handle AJAX forgot password
     * 
     * @param Request $request
     * @return \Growfund\Http\SiteResponse
     */
    public function ajax_forgot_password(Request $request)
    {
        $raw_data = [
            'email' => $request->get_email('email')
        ];

        $validator = Validator::make($raw_data, [
            'email' => 'required|email'
        ]);

        if ($validator->is_failed()) {
            throw ValidationException::with_errors($validator->get_errors()); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- validation exception intentionally ignored
        }

        $sanitized_data = Sanitizer::make($raw_data, [
            'email' => Sanitizer::EMAIL
        ])->get_sanitized_data();

        try {
            $this->auth_service->send_password_reset_email(['email' => $sanitized_data['email']]);

            $response = growfund_site_response();
            $json_response = $response->json([
                'message' => __('If the email address exists in our database, you will receive a password recovery link at your email address in a few minutes.', 'growfund'),
                'email' => $sanitized_data['email']
            ]);
            return $json_response;
        } catch (ValidationException $e) {
            wp_send_json_error([
                'message' => __('Please check your input and try again.', 'growfund'),
                'errors' => $e->get_errors()
            ], 422);
            return;
        } catch (Exception $e) {
            wp_send_json_error([
                'message' => __('We encountered an issue while processing your request. Please try again later.', 'growfund')
            ], 500);
            return;
        }
    }

    /**
     * Show reset password page
     * 
     * @param Request $request
     * @return string
     */
    public function show_reset_password(Request $request)
    {
        $raw_data = [
            'key' => $request->get_string('key'),
            'login' => $request->get_string('login')
        ];

        $validator = Validator::make($raw_data, [
            'key' => 'required|string',
            'login' => 'required|string'
        ]);

        if ($validator->is_failed()) {
            $this->redirect_with_error(__('Sorry, your reset password link is no longer valid. You can request another one below.', 'growfund'));
        }

        $user = get_user_by('login', $raw_data['login']);

        if (!$user || !$this->auth_service->is_valid_reset_key($user->ID, $raw_data['key'])) {
            $this->redirect_with_error(__('Sorry, your reset password link is no longer valid. You can request another one below.', 'growfund'));
        }

        if ($this->auth_service->is_reset_key_viewed($user->ID)) {
            $this->redirect_with_error(__('Sorry, your reset password link is no longer valid. You can request another one below.', 'growfund'));
        }

        $this->auth_service->mark_reset_key_viewed($user->ID);

        return growfund_renderer()->get_html('site.auth.reset-password', [
            'key' => $raw_data['key'],
            'login' => $raw_data['login']
        ]);
    }

    /**
     * Handle AJAX reset password
     * 
     * @param Request $request
     * @return \Growfund\Http\SiteResponse
     */
    public function ajax_reset_password(Request $request)
    {
        $raw_data = [
            'password' => $request->get_string('password'),
            'password_confirmation' => $request->get_string('password_confirmation'),
            'key' => $request->get_string('key'),
            'login' => $request->get_string('login')
        ];

        $validator = Validator::make($raw_data, ResetPasswordDTO::validation_rules());

        if ($validator->is_failed()) {
            throw ValidationException::with_errors($validator->get_errors()); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- validation exception intentionally ignored
        }

        $sanitized_data = Sanitizer::make($raw_data, ResetPasswordDTO::sanitization_rules())->get_sanitized_data();

        $reset_password_dto = ResetPasswordDTO::from_array($sanitized_data);

        $user = get_user_by('login', $reset_password_dto->login);

        if ($user && $this->auth_service->is_reset_key_consumed($user->ID)) {
            throw ValidationException::with_errors(['system' => esc_html__('This password reset link has already been used. Please request a new one.', 'growfund')]);
        }

        if ($user && !$this->auth_service->is_reset_key_viewed($user->ID)) {
            throw ValidationException::with_errors(['system' => esc_html__('This password reset link is invalid. Please request a new one.', 'growfund')]);
        }

        if ($this->auth_service->reset_password($reset_password_dto)) {
            return growfund_site_response()->json([
                'message' => __('Password has been reset successfully. You can now log in with your new password.', 'growfund')
            ]);
        }
    }

    /**
     * Redirect to forgot password page with error message
     * 
     * @param string $message
     * @return void
     */
    protected function redirect_with_error($message)
    {
        growfund_flash_set_message('growfund_reset_password_error', $message);
        return growfund_redirect(growfund_forget_password_url());
    }
}
