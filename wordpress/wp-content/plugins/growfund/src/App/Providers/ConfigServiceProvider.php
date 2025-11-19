<?php

namespace Growfund\App\Providers;

defined( 'ABSPATH' ) || exit;

use Growfund\Core\CurrencyConfig;
use Growfund\Core\MailConfig;
use Growfund\Core\ServiceProvider;

class ConfigServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(CurrencyConfig::class, function () {
            return new CurrencyConfig();
        });

        $this->app->singleton(MailConfig::class, function () {
            return new MailConfig();
        });
    }
}
