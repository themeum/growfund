<?php

namespace Growfund\Controllers\API;

defined( 'ABSPATH' ) || exit;

use Growfund\Exceptions\ValidationException;
use Growfund\Http\Request;
use Growfund\Http\Response;
use Growfund\Supports\Payment;
use Growfund\Validation\Validator;

class PaymentMethodController
{
    /**
     * Get all the available payment methods.
     *
     * @param Request $request
     * @return Response
     */
    public function get(Request $request)
    {
        $type = $request->get_string('type');

        $validator = Validator::make($request->all(), [
            'type' => 'string|in:online-payment,manual-payment,all',
        ]);

        if ($validator->is_failed()) {
            throw ValidationException::with_errors($validator->get_errors()); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- validation exception intentionally ignored
        }

        $payment_methods = Payment::get_active_payment_methods($type);

        return growfund_response()->json([
            'data' => $payment_methods,
            'message' => __('Payment methods fetched successfully!', 'growfund'),
        ]);
    }
}
