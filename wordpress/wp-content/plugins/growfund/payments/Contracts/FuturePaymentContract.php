<?php

namespace Growfund\Payments\Contracts;

defined( 'ABSPATH' ) || exit;

use Growfund\Payments\DTO\CustomerDTO;
use Growfund\Payments\DTO\PaymentPayloadDTO;
use Growfund\Payments\DTO\SavePaymentMethodPayloadDTO;
use Growfund\Payments\DTO\PaymentResponseDTO;
use Exception;

interface FuturePaymentContract
{
    /**
     * Create a setup session for saving payment methods (e.g., for future pledges).
     *
     * @param SavePaymentMethodPayloadDTO $payload
     * @return PaymentResponseDTO
     */
    public function save_payment_method(SavePaymentMethodPayloadDTO $payload);

    /**
     * Create a customer
     * 
     * @param CustomerDTO $customer_dto
     * @return string The customer ID
     * @throws \Exception
     */
    public function create_customer(CustomerDTO $customer_dto);

    /**
     * Charge a stored payment method
     * 
     * @param PaymentPayloadDTO $customer_dto
     * @return PaymentResponseDTO
     * @throws Exception
     */
    public function charge_stored_payment_method(PaymentPayloadDTO $payload);
}
