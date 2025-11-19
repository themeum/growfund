<?php

namespace Growfund;

defined( 'ABSPATH' ) || exit;

use Growfund\Exceptions\ValidationException;
use Growfund\Http\Response;
use Exception;

class ApiExceptionHandler
{
    public static function get_response(Exception $exception)
    {
        if ($exception instanceof ValidationException) {
            $response = [
                'message' => $exception->getMessage(),
                'errors' => $exception->get_errors(),
            ];

            return growfund_response()->json($response, Response::UNPROCESSABLE_ENTITY);
        }

        return static::fallback_response($exception);
    }

    protected static function fallback_response(Exception $exception)
    {
        $status_code = (int) $exception->getCode();

        if ($status_code < 100 || $status_code > 599) {
            $status_code = Response::INTERNAL_SERVER_ERROR; // fallback to 500
        }

        $response = [
            'message' => $exception->getMessage(),
        ];

        return growfund_response()->json($response, $status_code);
    }
}
