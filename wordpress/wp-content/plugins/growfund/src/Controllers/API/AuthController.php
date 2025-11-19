<?php

namespace Growfund\Controllers\API;

defined( 'ABSPATH' ) || exit;

use Exception;

class AuthController
{
    public function logout()
    {
        if (! growfund_user()) {
            throw new Exception(esc_html__('You are not logged in', 'growfund'));
        }

        growfund_logout();

        return growfund_response()->json([
            'data' => true,
            'message' => __('You have been logged out', 'growfund')
        ]);
    }
}
