<?php

namespace Growfund\Services;

defined( 'ABSPATH' ) || exit;

use Exception;
use Growfund\Constants\AppConfigKeys;
use Growfund\Constants\OptionKeys;
use Growfund\Core\AppSettings;
use Growfund\Core\CurrencyConfig;
use Growfund\Supports\Option;
use Growfund\Supports\Payment;

class AppConfigService
{
    public function get_config()
    {
        if (! is_user_logged_in()) {
            return $this->public_config();
        }

        $keys = AppConfigKeys::all();
        $config = [];

        foreach ($keys as $key) {
            $config[$key] = Option::get($key, null);
        }

        if (isset($config[AppConfigKeys::PAYMENT])) {
            $config[AppConfigKeys::PAYMENT] = array_merge(
                $config[AppConfigKeys::PAYMENT],
                growfund_app()->make(CurrencyConfig::class)->get()
            );

            $config[AppConfigKeys::PAYMENT]['e_commerce_engine'] = Payment::get_engine();
        }

        $config['growfund_current_user'] = growfund_user()->get_data();

        return $config;
    }

    protected function public_config()
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
