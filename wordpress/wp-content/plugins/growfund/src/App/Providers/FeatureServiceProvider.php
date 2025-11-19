<?php

namespace Growfund\App\Providers;

defined( 'ABSPATH' ) || exit;

use Growfund\Core\FeatureManager;
use Growfund\Core\ServiceProvider;

class FeatureServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(FeatureManager::class, function () {
            return new FeatureManager();
        });
    }
}
