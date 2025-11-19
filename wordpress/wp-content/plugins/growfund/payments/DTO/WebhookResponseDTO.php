<?php

namespace Growfund\Payments\DTO;

defined( 'ABSPATH' ) || exit;

use Growfund\DTO\DTO;

class WebhookResponseDTO extends DTO
{
    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $status;

    /**
     * @var string|null
     */
    public $transaction_id;

    /**
     * @var string|null
     */
    public $order_id;

    /**
     * @var float|null
     */
    public $amount;

    /**
     * @var float|null
     */
    public $fee;

    /**
     * @var string|null
     */
    public $currency;

    /**
     * @var string|null
     */
    public $payment_gateway;

    /**
     * @var string|null
     */
    public $customer_id;

    /**
     * @var array|null
     */
    public $metadata;

    /**
     * @var array|null
     */
    public $raw;

    /**
     * Constructor.
     *
     * @param array $data
     */
}
