<?php

namespace Growfund\Providers;

defined( 'ABSPATH' ) || exit;

use Growfund\Core\ServiceProvider;
use Growfund\Payments\GatewayDiscovery;

class PaymentServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(GatewayDiscovery::class, function () {
            return new GatewayDiscovery();
        });
    }

    public function boot()
    {
        $this->app->make(GatewayDiscovery::class)->discover($this->app);
    }
}
