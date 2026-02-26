<?php

namespace Growfund\Services;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\Status\PledgeStatus;
use Growfund\Payments\Contracts\FuturePaymentContract;
use Growfund\Payments\DTO\PaymentPayloadDTO;
use Growfund\Supports\Utils;

/**
 * Campaign tag service
 * @since 1.0.0
 */
class ChargeBackerService
{
    protected $data = [];


    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function charge()
    {
        $pledge_service = new PledgeService();
        $pledge_id = $this->data['pledge_id'] ?? null;
        $pledge = $pledge_service->get_by_id($pledge_id);
        $payment = $pledge->payment->get_values();

        // We will proceed the payment collection if and only if the pledge status is in-progress.
        if ($pledge->status !== PledgeStatus::IN_PROGRESS) {
            return;
        }

        $payment_gateway = growfund_payment_gateway($payment->payment_method->name, false);

        if (is_null($payment_gateway) || !$payment_gateway instanceof FuturePaymentContract) {
            return;
        }

        $payable_amount = $payment->raw_total;

        $payment_payload = new PaymentPayloadDTO([
            'amount' => $payable_amount,
            'currency' => Utils::get_currency(),
            'order_id' => $pledge->id,
            'payment_token_id' => $payment->transaction_id
        ]);

        $payment_gateway->charge_stored_payment_method($payment_payload);
    }
}
