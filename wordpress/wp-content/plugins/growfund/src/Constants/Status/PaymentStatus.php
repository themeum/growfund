<?php

namespace Growfund\Constants\Status;

defined( 'ABSPATH' ) || exit;

use Growfund\Traits\HasConstants;

class PaymentStatus
{
    use HasConstants;

    const PENDING = 'pending';
    const PAID = 'paid';
    const UNPAID = 'unpaid';
    const REFUNDED = 'refunded';
    const FAILED = 'failed';
}
