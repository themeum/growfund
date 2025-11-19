<?php

namespace Growfund\Payments\DTO;

defined( 'ABSPATH' ) || exit;

use Growfund\DTO\DTO;
use InvalidArgumentException;

class PaymentPayloadDTO extends DTO
{

    /** @var float */
    public $amount;

    /** @var string */
    public $currency;

    /** @var string */
    public $order_id;

    /** @var string */
    public $payment_token_id;

    /** @var string|null */
    public $description;

    /** @var string|null */
    public $redirect_url;

    /** @var string|null */
    public $success_url;

    /** @var string|null */
    public $cancel_url;

    /** @var array|null metadata excluding `order_id` */
    public $metadata;

    /** @var array|null */
    public $additional_data;

    public function __construct(array $data)
    {
        parent::__construct($data);

        $this->validate();
    }

    protected function validate()
    {
        if ($this->amount <= 0) {
            throw new InvalidArgumentException('Amount must be greater than 0.');
        }

        if (empty($this->currency)) {
            throw new InvalidArgumentException('Currency is required.');
        }

        if (!preg_match('/^[A-Z]{3}$/', $this->currency)) {
            throw new InvalidArgumentException('Currency must be a valid 3-letter ISO code.');
        }
    }
}
