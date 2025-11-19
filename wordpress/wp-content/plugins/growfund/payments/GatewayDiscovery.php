<?php

namespace Growfund\Payments;

defined( 'ABSPATH' ) || exit;

use Growfund\Application;
use Growfund\Core\AppSettings;

class GatewayDiscovery
{
    /**
     * Discovers and registers all the installed payment gateways.
     *
     * @return void
     */
    public function discover(Application $app)
    {
        $payment_settings = $app->make(AppSettings::PAYMENT);
        $payments = $payment_settings->get_installed_payment_gateways();

        foreach ($payments as $payment) {
            if (empty($payment->class)) {
                continue;
            }

            $app->bind($payment->class, function () use ($payment) {
                $gateway = new $payment->class($payment->config);
                return $gateway;
            });

            $app->alias($payment->name, $payment->class);
        }
    }
}
