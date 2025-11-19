<?php

namespace Growfund\App\Providers;

defined( 'ABSPATH' ) || exit;

use Growfund\Core\ServiceProvider;
use Growfund\App\Settings\AdvancedSettings;
use Growfund\App\Settings\BrandingSettings;
use Growfund\App\Settings\CampaignSettings;
use Growfund\App\Settings\EmailAndNotificationSettings;
use Growfund\App\Settings\GeneralSettings;
use Growfund\App\Settings\PaymentSettings;
use Growfund\App\Settings\PermissionSettings;
use Growfund\App\Settings\ReceiptSettings;
use Growfund\App\Settings\SecuritySettings;

class SettingsServiceProvider extends ServiceProvider
{
    public function register()
    {
        $setting_classes = [
            GeneralSettings::class,
            CampaignSettings::class,
            PaymentSettings::class,
            EmailAndNotificationSettings::class,
            PermissionSettings::class,
            ReceiptSettings::class,
            BrandingSettings::class,
            SecuritySettings::class,
            AdvancedSettings::class,
        ];

        foreach ($setting_classes as $setting_class) {
            $this->app->singleton($setting_class, function () use ($setting_class) {
                return new $setting_class();
            });
        }
    }
}
