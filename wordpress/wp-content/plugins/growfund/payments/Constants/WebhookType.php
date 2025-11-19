<?php

namespace Growfund\Payments\Constants;

defined( 'ABSPATH' ) || exit;

class WebhookType
{
    const PAYMENT = 'payment';
    const SETUP = 'setup';
    const REFUND = 'refund';
    const UNKNOWN = 'unknown';
}
