<?php

namespace Growfund\Payments\DTO;

defined( 'ABSPATH' ) || exit;

use Growfund\DTO\DTO;

class PaymentStatusDTO extends DTO
{
    public $status; // success, failed, pending, refunded
    public $transaction_id;
    public $amount;
    public $currency;
    public $raw;
}
