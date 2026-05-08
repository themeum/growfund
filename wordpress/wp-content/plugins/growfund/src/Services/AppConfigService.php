<?php

namespace Growfund\Services;

defined( 'ABSPATH' ) || exit;

use Exception;
use Growfund\Constants\AppConfigKeys;
use Growfund\Constants\HookNames;
use Growfund\Constants\OptionKeys;
use Growfund\Core\AppSettings;
use Growfund\Core\CurrencyConfig;
use Growfund\Supports\Option;
use Growfund\Supports\Payment;

class AppConfigService
{
    public function get_config()
    {
        if (!growfund_user()->is_logged_in()) {
            return $this->get_public_config();
        }

        if (growfund_user()->is_admin()) {
            return $this->get_admin_config();
        }

        return $this->get_default_config();
    }

    protected function get_admin_config()
    {
        $keys = AppConfigKeys::all();
        $config = [];

        foreach ($keys as $key) {
            $config[$key] = Option::get($key, null);
        }

        if (isset($config[AppConfigKeys::PAYMENT])) {
            $new_payment_data = array_merge(
                $config[AppConfigKeys::PAYMENT],
                [
                    'enable_platform_fee' => false,
                    'enable_guest_checkout' => false,
                    'minimum_balance_to_request_withdrawal' => null,
                    'fundraiser_withdrawal_options' => null,
                ],
                growfund_app()->make(CurrencyConfig::class)->get()
            );

            $new_payment_data['e_commerce_engine'] = Payment::get_engine();

            $config[AppConfigKeys::PAYMENT] = apply_filters(
                HookNames::GROWFUND_APP_PAYMENT_CONFIG_FILTER, 
                $new_payment_data, 
                $config[AppConfigKeys::PAYMENT]
            ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
        }

        return $config;
    }

    protected function get_default_config() {
        return [
            AppConfigKeys::IS_DONATION_MODE => Option::get(AppConfigKeys::IS_DONATION_MODE, '0'),
            AppConfigKeys::CAMPAIGN => growfund_settings(AppSettings::CAMPAIGNS)->get(),
            AppConfigKeys::GENERAL => growfund_settings(AppSettings::GENERAL)->get(),
            AppConfigKeys::BRANDING => growfund_settings(AppSettings::BRANDING)->get(),
            AppConfigKeys::PAYMENT => !growfund_user()->is_fundraiser() 
                ? growfund_app()->make(CurrencyConfig::class)->get() 
                : apply_filters(
                    HookNames::GROWFUND_APP_PAYMENT_CONFIG_FILTER, 
                    growfund_app()->make(CurrencyConfig::class)->get(), 
                    growfund_settings(AppSettings::PAYMENT)->get()
                ), // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
            AppConfigKeys::USER_PERMISSIONS => growfund_settings(AppSettings::PERMISSIONS)->get(),
        ];
    }

    protected function get_public_config()
    {
        return [
            AppConfigKeys::IS_DONATION_MODE => Option::get(AppConfigKeys::IS_DONATION_MODE, '0'),
            AppConfigKeys::GENERAL => [
                'organization' => [
                    'name' => growfund_settings(AppSettings::GENERAL)->get_organization_name(),
                    'location' => growfund_settings(AppSettings::GENERAL)->get_organization_location(),
                    'contact_email' => growfund_settings(AppSettings::GENERAL)->get_organization_contact_email(),
                ]
            ],
            AppConfigKeys::CAMPAIGN => [
                'allow_fund' => growfund_settings(AppSettings::CAMPAIGNS)->allow_fund(),
                'allow_tribute' => growfund_settings(AppSettings::CAMPAIGNS)->allow_tribute(),
                'allow_comments' => growfund_settings(AppSettings::CAMPAIGNS)->allow_comments(),
            ],
            AppConfigKeys::BRANDING => growfund_settings(AppSettings::BRANDING)->get(),
            AppConfigKeys::PAYMENT => growfund_app()->make(CurrencyConfig::class)->get(),
        ];
    }

    public function save_salutation() {
        $titles = ['Mr.', 'Mrs.', 'Dr.', 'Prof.', 'Rev.', 'Lady', 'Sir', 'Dame', 'Lord'];
        Option::add(OptionKeys::SALUTAION, $titles);
    }

    public function save_paypal_config() {
        try {
            $manifest_path = GROWFUND_DIR_PATH . 'gateways/Paypal/manifest.json';

			if (!file_exists($manifest_path)) {
				growfund_error_log('Manifest file not exist: ' . $manifest_path);

                return;
			}
    
			$manifest_file_content = file_get_contents($manifest_path);
            
			if (!growfund_is_valid_json($manifest_file_content)) {
				growfund_error_log('Invalid manifest file');

                return;
			}
    
			$manifest = json_decode($manifest_file_content, true);
			$manifest['config']['logo'] = GROWFUND_DIR_URL . 'gateways/Paypal/' . $manifest['config']['logo'];
			$service = new PaymentGatewayService();
			$service->store_gateway_info($manifest['name'], $manifest);
		} catch (Exception $error) {
			growfund_error_log($error->getMessage());
		}
	}
}
