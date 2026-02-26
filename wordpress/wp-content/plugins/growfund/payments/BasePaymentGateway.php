<?php

namespace Growfund\Payments;

use Growfund\Payments\Contracts\PaymentConfigurationContract;

defined( 'ABSPATH' ) || exit;

abstract class BasePaymentGateway implements PaymentConfigurationContract
{
    /**
     * The gateway is configured and ready to use
     * @return bool
     */
    abstract public function is_configured();
}
