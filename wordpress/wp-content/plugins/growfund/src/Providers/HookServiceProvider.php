<?php

namespace Growfund\Providers;

defined( 'ABSPATH' ) || exit;

use Growfund\Core\ServiceProvider;
use Growfund\Supports\Hook;

class HookServiceProvider extends ServiceProvider
{
    /**
     * Register the hooks to the application.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Add the action hooks on after the application booted.
     *
     * @return void
     */
    public function boot()
    {
        $hooks = require GROWFUND_DIR_PATH . 'configs/hooks.php';

        if (empty($hooks)) {
            return;
        }

        $hooks = array_merge(
            $hooks['actions'],
            $hooks['filters'],
            $hooks['schedulers']
        );
        
        Hook::register($hooks);
    }
}
