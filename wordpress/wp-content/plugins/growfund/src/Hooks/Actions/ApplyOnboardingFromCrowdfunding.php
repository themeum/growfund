<?php

namespace Growfund\Hooks\Actions;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\AppConfigKeys;
use Growfund\Constants\HookNames;
use Growfund\Constants\HookTypes;
use Growfund\Hooks\BaseHook;
use Growfund\Supports\Option;
use Growfund\Supports\Woocommerce;

class ApplyOnboardingFromCrowdfunding extends BaseHook
{
    public function get_name()
    {
        return HookNames::APPLY_ONBOARDING_FROM_CROWDFUNDING;
    }

    public function get_type()
    {
        return HookTypes::ACTION;
    }

    public function handle(...$args)
    {
        if (growfund_app()->is_onboarding_completed()) {
            return;
        }
        
        $woocommerce_config = Woocommerce::get_config();

        $general_settings = [
            'country' => $woocommerce_config['country'] ?? null,
        ];

		$payment_settings = [
            'e_commerce_engine' => 'woo-commerce',
            'currency' => $woocommerce_config['currency'] ?? '$:USD',
            "currency_position" => $woocommerce_config['currency_position'] ?? null,
            "decimal_separator" => $woocommerce_config['decimal_separator'] ?? null,
            "thousand_separator" => $woocommerce_config['thousand_separator'] ?? null,
            "decimal_places" => $woocommerce_config['decimal_places'] ?? null,
        ];
        

        $is_successful = Option::store_default_settings([
            AppConfigKeys::PAYMENT => $payment_settings,
            AppConfigKeys::GENERAL => $general_settings,
            AppConfigKeys::IS_DONATION_MODE => 0,
        ]);

        if ($is_successful) {
            Option::update(
                AppConfigKeys::IS_ONBOARDING_COMPLETED,
                1
            );

            do_action(HookNames::ONBOARDING_COMPLETED); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
        }
    }
}
