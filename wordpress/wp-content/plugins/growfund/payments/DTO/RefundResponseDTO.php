<?php

namespace Growfund\Payments\DTO;

defined( 'ABSPATH' ) || exit;

use Growfund\DTO\DTO;

class RefundResponseDTO extends DTO
{
    public $success;
    public $refund_id;
    public $transaction_id;
    public $amount;
    public $raw;
}
