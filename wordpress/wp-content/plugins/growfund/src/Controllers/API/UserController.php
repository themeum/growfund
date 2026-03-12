<?php

namespace Growfund\Controllers\API;

use Growfund\Constants\HookNames;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\UserDeleteType;
use Growfund\DTO\Backer\UpdateBackerNotificationsDTO;
use Growfund\DTO\Donor\UpdateDonorNotificationsDTO;
use Growfund\DTO\Fundraiser\UpdateFundraiserNotificationsDTO;
use Growfund\DTO\User\UpdatePasswordDTO;
use Growfund\Exceptions\ValidationException;
use Growfund\Http\Request;
use Growfund\Http\Response;
use Growfund\Sanitizer;
use Growfund\Services\BackerService;
use Growfund\Services\UserService;
use Growfund\Supports\User;
use Growfund\Validation\Validator;
use Exception;

class UserController
{
    /** @var UserService */
    protected $service;
    protected $backer_service;

    /**
     * UserController constructor.
     *
     * @param UserService $service
     */
    public function __construct(UserService $service, BackerService $backer_service)
    {
        $this->service = $service;
        $this->backer_service = $backer_service;
    }

    /**
     * Validate the user email.
     *
     * @since 1.0.0
     *
     * @param Request $request
     * @return Response
     */
    public function validate_email(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->is_failed()) {
            throw ValidationException::with_errors($validator->get_errors(), esc_html__('Invalid email address!', 'growfund')); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- validation exception intentionally ignored
        }

        $user = get_user_by('email', $request->get_email('email'));

        if ($user && !User::is_soft_deleted($user->ID ?? null)) {
            return growfund_response()->json([
                'data' => false,
                'message' => __('Email is already in use!', 'growfund'),
            ], Response::BAD_REQUEST);
        }

        return growfund_response()->json([
            'data' => true,
            'message' => __('Email is available!', 'growfund'),
        ], Response::OK);
    }

    /**
     * Validate the username.
     *
     * @since 1.0.0
     *
     * @param Request $request
     * @return Response
     */
    public function validate_username(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
        ]);

        if ($validator->is_failed()) {
            throw ValidationException::with_errors($validator->get_errors(), esc_html__('Invalid username!', 'growfund')); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- validation exception intentionally ignored
        }

        $user = get_user_by('login', $request->get_string('username'));

        if ($user && !User::is_soft_deleted($user->ID ?? null)) {
            return growfund_response()->json([
                'data' => false,
                'message' => __('Username is already in use!', 'growfund'),
            ], Response::BAD_REQUEST);
        }

        return growfund_response()->json([
            'data' => true,
            'message' => __('Username is available!', 'growfund'),
        ], Response::OK);
    }

    /**
     * Get the current user.
     *
     * @since 1.0.0
     *
     * @return Response
     */
    public function get_current_user()
    {
        $user = apply_filters(HookNames::GROWFUND_CURRENT_USER_FILTER, $this->service->get_current_user()->to_array());

        return growfund_response()->json([
            'data' => $user,
        ], Response::OK);
    }

    /**
     * Update Backer or Donor notification settings.
     *
     * @param Request $request
     * @return Response
     */
    public function update_notifications(Request $request)
    {
        $id = $request->get_int('id');
        $data = $request->all();
        $user = growfund_user($id);

        $validation_rules = [];
        $sanitization_rules = [];

        if ($user->is_fundraiser()) {
            $validation_rules = UpdateFundraiserNotificationsDTO::validation_rules();
            $sanitization_rules = UpdateFundraiserNotificationsDTO::sanitization_rules();
        }

        if ($user->is_backer()) {
            $validation_rules = UpdateBackerNotificationsDTO::validation_rules();
            $sanitization_rules = UpdateBackerNotificationsDTO::sanitization_rules();
        }

        if ($user->is_donor()) {
            $validation_rules = UpdateDonorNotificationsDTO::validation_rules();
            $sanitization_rules = UpdateDonorNotificationsDTO::sanitization_rules();
        }

        $validator = Validator::make($data, $validation_rules);

        if ($validator->is_failed()) {
            throw ValidationException::with_errors(
                $validator->get_errors(), // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- validation exception intentionally ignored
                esc_html__('Invalid notifications!', 'growfund')
            );
        }

        $sanitized_data = Sanitizer::make($data, $sanitization_rules)->get_sanitized_data();

        $dto = null;

        if ($user->is_fundraiser()) {
            $dto = UpdateFundraiserNotificationsDTO::from_array($sanitized_data);
        }

        if ($user->is_backer()) {
            $dto = UpdateBackerNotificationsDTO::from_array($sanitized_data);
        }

        if ($user->is_donor()) {
            $dto = UpdateDonorNotificationsDTO::from_array($sanitized_data);
        }

        $is_updated = $this->service->update_notifications(
            $id,
            $dto
        );

        if (!$is_updated) {
            return growfund_response()->json([
                'data' => false,
                'message' => __('Failed to update notifications!', 'growfund'),
            ], Response::BAD_REQUEST);
        }

        return growfund_response()->json([
            'data' => true,
            'message' => __('Notifications updated successfully!', 'growfund'),
        ], Response::OK);
    }

    /**
     * Update the user's password.
     *
     * Validates and sanitizes the input data and updates the password
     * using the UserService. Returns a JSON response indicating the
     * success or failure of the operation.
     *
     * @param Request $request
     * @return Response
     * @throws ValidationException
     */
    public function update_password(Request $request)
    {
        $data = $request->all();

        $validator = Validator::make($data, UpdatePasswordDTO::validation_rules());

        if ($validator->is_failed()) {
            throw ValidationException::with_errors($validator->get_errors()); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- validation exception intentionally ignored
        }

        $sanitized_data = Sanitizer::make($data, UpdatePasswordDTO::sanitization_rules())
            ->get_sanitized_data();

        $dto = UpdatePasswordDTO::from_array($sanitized_data);

        if (!growfund_user()->is_admin()) {
            $dto->id = growfund_user()->get_id();
        }

        $is_success = $this->service->update_password($dto);

        $response = [
            'data' => $is_success,
            'message' => __('Password updated successfully.', 'growfund'),
        ];

        return growfund_response()->json($response, Response::CREATED);
    }

    /**
     * Send a password reset link to the specified user by user ID.
     *
     * If the current user is an admin, the user ID is taken from the request.
     * Otherwise, the current user ID is used.
     *
     * @param Request $request
     * @return Response
     */
    public function send_reset_password_email(Request $request)
    {
        $is_success = $this->service->send_password_reset_link_by_user_id($request->get_int('id'));

        return growfund_response()->json([
            'data' => $is_success,
            'message' => __('Password reset link sent successfully.', 'growfund'),
        ], Response::CREATED);
    }

    /**
     * Delete backer
     * @param Request $request
     * @return Response
     */
    public function delete_my_account()
    {
        if (!growfund_user() || growfund_user()->is_admin()) {
            throw new Exception(esc_html__('You don\'t have permission to take this action.', 'growfund'), (int) Response::UNAUTHORIZED);
        }

        $delete_type =  UserDeleteType::ANONYMIZE;

        $is_deleted = $this->service->delete(growfund_user()->get_id(), $delete_type);

        if ($is_deleted) {
            growfund_logout();
        }

        $response = [
            'data' => $is_deleted,
            'message' => $is_deleted ? __('User deleted successfully', 'growfund') : __('Failed to delete the user', 'growfund'),
        ];

        return growfund_response()->json($response, $is_deleted ? Response::OK : Response::CONFLICT);
    }
}
