<?php

defined( 'ABSPATH' ) || exit;

// Register your custom service providers here
// e.g. [CustomServiceProvider::class, AnotherServiceProvider::class, ...]

use Growfund\App\Providers\ConfigServiceProvider;
use Growfund\App\Providers\FeatureServiceProvider;
use Growfund\App\Providers\SettingsServiceProvider;
use Growfund\App\Providers\ShortcodeServiceProvider;

return [
    SettingsServiceProvider::class,
    ConfigServiceProvider::class,
    ShortcodeServiceProvider::class,
    FeatureServiceProvider::class,
];
