<?php

namespace Growfund\Controllers\Site;

defined( 'ABSPATH' ) || exit;

use Growfund\Contracts\Request;
use Growfund\Constants\UserTypes\Backer;
use Growfund\Constants\UserTypes\Donor;
use Growfund\Constants\UserTypes\Fundraiser;

use Growfund\DTO\Auth\LoginDTO;
use Growfund\DTO\Auth\RegisterDTO;
use Growfund\DTO\Auth\ResetPasswordDTO;
use Growfund\Services\AuthService;
use Growfund\Validation\Validator;
use Growfund\Exceptions\ValidationException;
use Growfund\Sanitizer;
use Exception;
use Growfund\Supports\Auth;
use Growfund\Supports\Url;
use Growfund\Views\Components\Auth\ForgotPassword;
use Growfund\Views\Components\Auth\Login;
use Growfund\Views\Components\Auth\ResetLink;
use Growfund\Views\Components\Auth\ResetPassword;
use Growfund\Views\Components\Auth\Signup;

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
        $login_page = new Login();

        return growfund_get_html($login_page);
    }

    /**
     * 
     * @param Request $request
     * @return void
     */
    public function login(Request $request)
    {
        $raw_data = [
            'user_login' => $request->get_string('user_login'),
            'password' => $request->get_string('password'),
        ];

        $validator = Validator::make($raw_data, LoginDTO::validation_rules());

        if ($validator->is_failed()) {
            growfund_flash_set_message('login_form_old', $raw_data);
            growfund_flash_set_message('login_form_errors', $validator->get_errors());
            growfund_redirect(growfund_login_url());
            return;
        }

        $sanitized_data = Sanitizer::make($raw_data, LoginDTO::sanitization_rules())->get_sanitized_data();

        $login_dto = LoginDTO::from_array($sanitized_data);

        try {
			$redirect_url = $this->auth_service->login($login_dto);
        } catch (ValidationException $error) {
            growfund_flash_set_message('login_form_old', $raw_data);
            growfund_flash_set_message('login_form_errors', $error->get_errors());
            growfund_redirect(growfund_login_url());
            return;
        } catch (Exception $error) {
            growfund_flash_set_message('login_form_old', $raw_data);
			growfund_flash_set_message('login_error', $error->getMessage());
            growfund_redirect(growfund_login_url());
            return;
        }
        
        $redirect_to = $request->get_string('redirect_to');

        if (!empty($redirect_to) && growfund_is_valid_url($redirect_to)) {
            growfund_redirect($redirect_to);
        }

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
        $signup_page = new Signup();
        
        return growfund_get_html($signup_page);
    }

    /**
     * registration handler
     * 
     * @param Request $request
     * @return \Growfund\Http\SiteResponse
     */
    public function register(Request $request)
    {
        $user_type = $request->get_string('user_type', 'regular');

        $role = growfund_app()->is_donation_mode() ? Donor::ROLE : Backer::ROLE;

        if ($user_type === 'fundraiser') {
            $role = Fundraiser::ROLE;
        }
        
        $raw_data = [
            'first_name' => $request->get_string('first_name'),
            'last_name' => $request->get_string('last_name'),
            'email' => $request->get_email('email'),
            'username' => $request->get_string('username'),
            'password' => $request->get_string('password'),
            'password_confirmation' => $request->get_string('password_confirmation'),
            'role' => $role,
        ];

        $validator = Validator::make($raw_data, RegisterDTO::validation_rules());

        if ($validator->is_failed()) {
            growfund_flash_set_message('registration_form_old', $raw_data);
            growfund_flash_set_message('registration_form_errors', $validator->get_errors());
            Url::redirect_back();
            return;
        }

        $sanitized_data = Sanitizer::make($raw_data, RegisterDTO::sanitization_rules())->get_sanitized_data();

        $register_dto = RegisterDTO::from_array($sanitized_data);

        try {
			$is_registered = $this->auth_service->register($register_dto);
        } catch (ValidationException $validator) {
            growfund_flash_set_message('registration_form_old', $raw_data);
			growfund_flash_set_message('registration_form_errors', $validator->get_errors());
            Url::redirect_back();
            return;
        } catch (Exception $e) {
            growfund_flash_set_message('registration_form_old', $raw_data);
            growfund_flash_set_message('registration_error', $e->getMessage());
            Url::redirect_back();
            return;
        }
        

        if ($is_registered) {
            growfund_redirect(growfund_login_url());
            return;
        }

        growfund_flash_set_message('registration_form_old', $raw_data);
        growfund_flash_set_message('registration_error', esc_html__('Registration failed!', 'growfund'));
        Url::redirect_back();
    }

    /**
     * Show forgot password page
     * 
     * @return string
     */
    public function show_forgot_password()
    {
        $forgot_password = new ForgotPassword();
        
        return growfund_get_html($forgot_password);
    }

    /**
     * Handle forgot password
     * 
     * @param Request $request
     * @return \Growfund\Http\SiteResponse
     */
    public function forgot_password(Request $request)
    {
        $raw_data = [
            'email' => $request->get_email('email')
        ];

        $validator = Validator::make($raw_data, [
            'email' => 'required|email'
        ]);

        if ($validator->is_failed()) {
            growfund_flash_set_message('forgot_password_form_old', $raw_data);
            growfund_flash_set_message('forgot_password_form_errors', $validator->get_errors());
            Url::redirect_back();

            return;
        }

        $sanitized_data = Sanitizer::make($raw_data, [
            'email' => Sanitizer::EMAIL
        ])->get_sanitized_data();

        try {
            $this->auth_service->send_password_reset_email(['email' => $sanitized_data['email']]);

			growfund_flash_set_message('reset_password_link_sent', 'true');

            growfund_redirect(growfund_url(Auth::reset_link_url(), [
				'sent' => true,
				'email' => urlencode($sanitized_data['email'])
			]));
        } catch (ValidationException $validator) {
            growfund_flash_set_message('forgot_password_form_old', $raw_data);
            growfund_flash_set_message('forgot_password_form_errors', $validator->get_errors());
            Url::redirect_back();
            return;
        } catch (Exception $e) {
            growfund_flash_set_message('forgot_password_error', __('We encountered an issue while processing your request. Please try again later.', 'growfund'));
            Url::redirect_back();
            return;
        }
    }

    public function show_reset_password_link(Request $request) {
        $resent_link_sent = $request->get_bool('sent', false);
        $email = urldecode($request->get_string('email', ''));

        if (!$resent_link_sent || !$email) {
            growfund_redirect(site_url());
            return;
        }

        $reset_link = new ResetLink();
        $reset_link->user_email = $email;

        return growfund_get_html($reset_link);
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
            growfund_flash_set_message('forgot_password_error', __('Sorry, your reset password link is no longer valid. You can request another one below.', 'growfund'));
			growfund_redirect(growfund_forget_password_url());

            return;
        }

        $user = get_user_by('login', $raw_data['login']);

        if (!$user || !$this->auth_service->is_valid_reset_key($user->ID, $raw_data['key'])) {
            growfund_flash_set_message('forgot_password_error', __('Sorry, your reset password link is no longer valid. You can request another one below.', 'growfund'));
			growfund_redirect(growfund_forget_password_url());

            return;
        }

        if ($this->auth_service->is_reset_key_consumed($user->ID)) {
            growfund_flash_set_message('forgot_password_error', __('Sorry, your reset password link is no longer valid. You can request another one below.', 'growfund'));
			growfund_redirect(growfund_forget_password_url());

            return;
        }

        $reset_password = new ResetPassword();
        $reset_password->username = $user->user_login;
        $reset_password->token = $raw_data['key'];

        return growfund_get_html($reset_password);
    }

    /**
     * Handle reset password
     * 
     * @param Request $request
     * @return \Growfund\Http\SiteResponse
     */
    public function reset_password(Request $request)
    {
        $raw_data = [
            'password' => $request->get_string('password'),
            'password_confirmation' => $request->get_string('password_confirmation'),
            'key' => $request->get_string('key'),
            'login' => $request->get_string('login')
        ];

        $validator = Validator::make($raw_data, ResetPasswordDTO::validation_rules());

        
        if ($validator->is_failed()) {
            growfund_flash_set_message('reset_password_form_old', $raw_data);
            growfund_flash_set_message('reset_password_form_errors', $validator->get_errors());
            growfund_redirect(growfund_url(growfund_reset_password_url(), [
				'key' => $raw_data['key'],
				'login' => urlencode($raw_data['login'])
			]));

            return;
        }

        $sanitized_data = Sanitizer::make($raw_data, ResetPasswordDTO::sanitization_rules())->get_sanitized_data();

        $reset_password_dto = ResetPasswordDTO::from_array($sanitized_data);

        $user = get_user_by('login', $reset_password_dto->login);

        if (!$user || $this->auth_service->is_reset_key_consumed($user->ID)) {
            growfund_flash_set_message('forgot_password_error', __('Sorry, your reset password link is no longer valid. You can request another one below.', 'growfund'));
			growfund_redirect(growfund_forget_password_url());

            return;
        }

        if ($this->auth_service->reset_password($reset_password_dto, $user->ID)) {
            growfund_redirect(growfund_login_url());

            return;
        }
        
        growfund_flash_set_message('reset_password_error', __('Failed to reset your password. Please try again.', 'growfund'));
		growfund_redirect(growfund_url(growfund_reset_password_url(), [
			'key' => $raw_data['key'],
			'login' => urlencode($raw_data['login'])
		]));
    }
}
