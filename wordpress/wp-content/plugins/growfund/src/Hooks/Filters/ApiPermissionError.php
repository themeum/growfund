<?php

namespace Growfund\Hooks\Filters;

defined( 'ABSPATH' ) || exit;

use WP_Error;
use Growfund\Constants\HookNames;
use Growfund\Constants\HookTypes;
use Growfund\Hooks\BaseHook;

class ApiPermissionError extends BaseHook
{
    public function get_name()
    {
        return HookNames::REST_REQUEST_AFTER_CALLBACKS;
    }

    public function get_type()
    {
        return HookTypes::FILTER;
    }

    public function handle(...$args)
    {
        $response = $args[0];

        if ($response instanceof WP_Error) {

            $data = $response->get_error_data();

            return growfund_response()->json([
                'message' => $response->get_error_message(),
                'errors' => $response->get_error_messages(),
            ], $data['status']);
        }

        return $response;
    }
}
