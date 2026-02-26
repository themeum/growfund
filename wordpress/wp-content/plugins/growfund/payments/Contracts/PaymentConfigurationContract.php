<?php

namespace Growfund\Payments\Contracts;

defined( 'ABSPATH' ) || exit;

interface PaymentConfigurationContract
{
    /**
     * The gateway is configured and ready to use
     * @return bool
     */
    public function is_configured();
}
