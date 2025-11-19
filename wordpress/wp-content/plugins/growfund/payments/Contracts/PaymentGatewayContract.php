<?php

namespace Growfund\Payments\Contracts;

defined( 'ABSPATH' ) || exit;

use Growfund\Payments\DTO\PaymentPayloadDTO;
use Growfund\Payments\DTO\PaymentResponseDTO;
use Growfund\Payments\DTO\WebhookResponseDTO;
use Growfund\Payments\DTO\RefundResponseDTO;
use Growfund\Payments\DTO\PaymentStatusDTO;

interface PaymentGatewayContract
{
    /**
     * Set credentials or configuration options (e.g., API keys, environment).
     *
     * @param array $config
     * @return void
     */
    public function set_config(array $config);

    /**
     * Initiate an immediate or redirect-based payment.
     *
     * @param PaymentPayloadDTO $payload
     * @return PaymentResponseDTO
     */
    public function charge(PaymentPayloadDTO $payload);

    /**
     * Issue a full or partial refund for a completed transaction.
     *
     * @param string $transaction_id
     * @param float|null $amount
     * @return RefundResponseDTO
     */
    public function refund($transaction_id, $amount = null, $currency = null);

    /**
     * Retrieve the status of a transaction by its ID.
     *
     * @param string $transaction_id
     * @return PaymentStatusDTO
     */
    public function verify($transaction_id);

    /**
     * Confirm the completion of a transaction.
     *
     * @param mixed $data
     * @return PaymentResponseDTO|null
     */
    public function confirm($data);

    /**
     * Handle incoming webhook payloads and return standardized result.
     *
     * @param string $body
     * @param array $headers
     * @return WebhookResponseDTO
     */
    public function handle_webhook($body, $headers);

    /**
     * Format the amount for the payment gateway
     *
     * @param string $amount
     * @return int|float
     */
    public function format_amount($amount);

    /**
     * Parse the amount from the payment gateway
     *
     * @param string $amount
     * @return int|float
     */
    public function parse_amount($amount);
}
