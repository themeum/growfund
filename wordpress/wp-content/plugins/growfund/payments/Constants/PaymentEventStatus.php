<?php

namespace Growfund\Payments\Constants;

defined( 'ABSPATH' ) || exit;

class PaymentEventStatus
{
    const PENDING = 'pending';
    const SUCCESS = 'success';
    const FAILED = 'failed';
    const CANCELLED = 'cancelled';
}
