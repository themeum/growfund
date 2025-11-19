<?php

defined( 'ABSPATH' ) || exit;

use Growfund\Core\AppSettings;

return [
    AppSettings::GENERAL => [Growfund\App\Settings\GeneralSettings::class],
    AppSettings::PAYMENT => [Growfund\App\Settings\PaymentSettings::class],
    AppSettings::NOTIFICATIONS => [Growfund\App\Settings\EmailAndNotificationSettings::class],
    AppSettings::CAMPAIGNS => [Growfund\App\Settings\CampaignSettings::class],
    AppSettings::PERMISSIONS => [Growfund\App\Settings\PermissionSettings::class],
    AppSettings::RECEIPTS => [Growfund\App\Settings\ReceiptSettings::class],
    AppSettings::SECURITY => [Growfund\App\Settings\SecuritySettings::class],
    AppSettings::ADVANCED => [Growfund\App\Settings\AdvancedSettings::class],
    AppSettings::BRANDING => [Growfund\App\Settings\BrandingSettings::class],
];
