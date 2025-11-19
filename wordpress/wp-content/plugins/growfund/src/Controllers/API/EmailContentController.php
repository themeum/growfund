<?php

namespace Growfund\Controllers\API;

defined( 'ABSPATH' ) || exit;

use Growfund\Contracts\Request;
use Growfund\Core\MailConfig;
use Growfund\Exceptions\ValidationException;
use Growfund\Http\Response;
use Growfund\Supports\Option;
use Growfund\Validation\Validator;

class EmailContentController
{
    protected $mail_config;

    public function __construct(MailConfig $config)
    {
        $this->mail_config = $config;
    }

    /**
     * Get the value of the specified email type.
     *
     * @param Request $request
     *
     * @return Response
     *
     * @throws ValidationException
     */
    public function get(Request $request)
    {
        $key = $request->get_string('key');

        $validator = Validator::make($request->all(), [
            'key' => 'required|in:' . implode(',', $this->mail_config->get_content_keys()),
        ]);

        if ($validator->is_failed()) {
            throw ValidationException::with_errors($validator->get_errors()); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- validation exception intentionally ignored
        }

        $email_content = $this->mail_config->content($key) ?? [];

        return growfund_response()->json([
            'data' => $email_content,
            'message' => '',
        ], Response::OK);
    }

    /**
     * Store or update the email content.
     *
     * @param Request $request
     *
     * @return Response
     *
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'key' => 'required|in:' . implode(',', $this->mail_config->get_content_keys()),
            'data' => 'required',
        ]);

        if ($validator->is_failed()) {
            throw ValidationException::with_errors($validator->get_errors()); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- validation exception intentionally ignored
        }

        $key = $request->get_string('key');
        $current_content = $this->mail_config->content($key) ?? [];

        $new_content = $request->get_array('data');

        $new_content['shortcodes'] = $current_content['shortcodes'] ?? [];
        $new_content['is_default'] = false;

        Option::update($key, $new_content);

        return growfund_response()->json([
            'data' => [],
            'message' => __('Email content saved!', 'growfund'),
        ], Response::OK);
    }

    /**
     * restore the email content.
     *
     * @param Request $request
     *
     * @return Response
     *
     * @throws ValidationException
     */
    public function restore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'key' => 'required|in:' . implode(',', $this->mail_config->get_content_keys()),
        ]);

        if ($validator->is_failed()) {
            throw ValidationException::with_errors($validator->get_errors()); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- validation exception intentionally ignored
        }

        $key = $request->get_string('key');

        Option::store_default_email_templates($key);

        return growfund_response()->json([
            'data' => [],
            'message' => __('Email content restored!', 'growfund'),
        ], Response::OK);
    }
}
