<?php

namespace Growfund\Controllers\API;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\HookNames;
use Growfund\Constants\OptionKeys;
use Growfund\Contracts\Request;
use Growfund\Exceptions\ValidationException;
use Growfund\Http\Response;
use Growfund\Supports\Option;
use Growfund\Validation\Validator;

class OptionController
{
    /**
     * Get the value of the specified option.
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
            'key' => 'required|in:' . implode(',', OptionKeys::get_constant_values()),
        ]);

        if ($validator->is_failed()) {
            throw ValidationException::with_errors($validator->get_errors()); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- validation exception intentionally ignored
        }

        $option_data = Option::get($key, []);

        return growfund_response()->json([
            'data' => $option_data,
            'message' => '',
        ], Response::OK);
    }

    /**
     * Store or update the option.
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
            'key' => 'required|in:' . implode(',', OptionKeys::get_constant_values()),
            'data' => 'required',
        ]);

        if ($validator->is_failed()) {
            throw ValidationException::with_errors($validator->get_errors()); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- validation exception intentionally ignored
        }

        $key = $request->get_string('key');
        $data = $this->get_data_from_request($request);

        Option::update($key, $data);

        return growfund_response()->json([
            'data' => [],
            'message' => __('Option saved!', 'growfund'),
        ], Response::OK);
    }

    protected function get_data_from_request($request) {
        $key = $request->get_string('key');
        
        if (OptionKeys::CHECKED_MIGRATION_CONSENT === $key) {
            return $request->get_bool('data');
        }

        $data = $request->get_array('data');
        $data = $this->merge_options($key, $data);

        return $data;
    }

    protected function merge_options($key, $data)
    {
        $new_data = $data;
        
        switch ($key) {
            case OptionKeys::PDF_PLEDGE_RECEIPT_TEMPLATE:
			case OptionKeys::PDF_DONATION_RECEIPT_TEMPLATE:
				$default_options = Option::get_default_pdf_templates($key);
                $new_data['colors'] = $default_options['colors'];
                $new_data['content']['signature'] = $default_options['content']['signature'];
                $new_data['content']['tax_information'] = $default_options['content']['tax_information'];
                break;
            case OptionKeys::EMAIL_NOTIFICATION_TEMPLATE:
                $new_data['colors'] = Option::get($key)['colors'] ?? [
                    'background' => '',
                    'button' => '',
                    'button_background' => '',
                    'label' => '',
                    'link' => '',
                    'text' => '',
                ];
                break;
            default:
                break;
        }

        $new_data = apply_filters(HookNames::GROWFUND_BEFORE_OPTION_UPDATE_FILTER, $new_data, $data); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound

        return $new_data;
    }
}
