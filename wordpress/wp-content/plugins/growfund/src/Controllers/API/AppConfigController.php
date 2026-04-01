<?php

namespace Growfund\Controllers\API;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\AppConfigKeys;
use Growfund\Constants\HookNames;
use Growfund\Contracts\Request;
use Growfund\DTO\OnboardingDTO;
use Growfund\Exceptions\ValidationException;
use Growfund\Http\Response;
use Growfund\Sanitizer;
use Growfund\Services\AppConfigService;
use Growfund\Supports\Option;
use Growfund\Supports\RewriteRule;
use Growfund\Supports\Woocommerce;
use Growfund\Validation\Validator;

/**
 * Controller responsible for handling application configuration settings.
 *
 * Provides endpoints for retrieving and updating app configuration.
 *
 * @since 1.0.0
 */
class AppConfigController
{
    /**
     * @var AppConfigService
     */
    protected $service;

    public function __construct(AppConfigService $service)
    {
        $this->service = $service;
    }


    /**
     * Get the current application configuration.
     *
     * @return Response JSON response containing app configuration data.
     */
    public function get()
    {
        return growfund_response()->json([
            'data' => $this->service->get_config(),
            'message' => __('App config data fetched!', 'growfund'),
        ], Response::OK);
    }

    /**
     * Store or update the application configuration.
     *
     * @param Request $request The HTTP request instance.
     *
     * @return Response JSON response indicating success or failure.
     */
    public function store(Request $request)
    {
        $key = $request->get_string('key');
        $data = $request->get_array('data');

        if (empty($key) || empty($data)) {
            return growfund_response()->json([
                'errors' => [],
                'message' => __('Key and value are required!', 'growfund'),
            ], Response::BAD_REQUEST);
        }

        if (!in_array($key, AppConfigKeys::all(), true)) {
            return growfund_response()->json([
                'errors' => [],
                /* translators: %s: Settings key */
                'message' => sprintf(__('Invalid settings key: %s provided.', 'growfund'), $key),
            ], Response::BAD_REQUEST);
        }

        $data = $this->merge_app_config($key, $data);

        Option::update($key, $data);

        RewriteRule::schedule_reset();

        $messages = [
            AppConfigKeys::GENERAL => __('General settings saved!', 'growfund'),
            AppConfigKeys::PAGE => __('Page settings saved!', 'growfund'),
            AppConfigKeys::CAMPAIGN => __('Campaign settings saved!', 'growfund'),
            AppConfigKeys::USER_PERMISSIONS => __('User and permissions settings saved!', 'growfund'),
            AppConfigKeys::PAYMENT => __('Payment settings saved!', 'growfund'),
            AppConfigKeys::PDF_RECEIPT => __('Receipt settings saved!', 'growfund'),
            AppConfigKeys::EMAIL_AND_NOTIFICATIONS => __('Email notifications settings saved!', 'growfund'),
            AppConfigKeys::INTEGRATIONS => __('Integrations settings saved!', 'growfund'),
            AppConfigKeys::SECURITY => __('Security settings saved!', 'growfund'),
            AppConfigKeys::BRANDING => __('Branding settings updated!', 'growfund'),
            AppConfigKeys::ADVANCED => __('Advanced settings updated!', 'growfund'),
        ];

        return growfund_response()->json([
            'data' => [],
            'message' => $messages[$key],
        ], Response::OK);
    }

    /**
     * Handle the onboarding process.
     *
     * @param Request $request
     * @return Response
     */
    public function onboarding(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($data, OnboardingDTO::validation_rules());

        if ($validator->is_failed()) {
            throw ValidationException::with_errors($validator->get_errors()); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- validation exception intentionally ignored
        }

        $sanitized_data = Sanitizer::make($data, OnboardingDTO::sanitization_rules());
        $dto = OnboardingDTO::from_array($sanitized_data->get_sanitized_data());

        $payment_settings = [
            'e_commerce_engine' => $dto->payment_mode,
            'currency' => $dto->currency,
        ];
        $general_settings = [
            'country' => $dto->base_country,
        ];

        $is_successful = Option::store_default_settings([
            AppConfigKeys::PAYMENT => $payment_settings,
            AppConfigKeys::GENERAL => $general_settings,
            AppConfigKeys::IS_DONATION_MODE => $dto->campaign_mode === 'donation' ? 1 : 0,
        ]);
        
        if ($is_successful) {
            Option::update(
                AppConfigKeys::IS_ONBOARDING_COMPLETED,
                1
            );

            do_action(HookNames::ONBOARDING_COMPLETED); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
        } else {
            // Revert back the settings if any of the settings is not set.
            Option::delete_all_settings();

            return growfund_response()->json([
                'data' => false,
                'message' => __('Onboarding failed! Something went wrong on saving the data.', 'growfund'),
            ], Response::INTERNAL_SERVER_ERROR);
        }

        return growfund_response()->json([
            'data' => true,
            'message' => __('Onboarding completed!', 'growfund'),
        ], Response::OK);
    }

    protected function merge_app_config($key, $data)
    {
        $app_config = Option::get($key, []);
        $data = array_merge($app_config, $data);

        $new_data = $data;

        switch ($key) {
            case AppConfigKeys::GENERAL:
                $new_data = array_merge($new_data, [
                    'company_field_visibility' => 'disabled',
                    'title_prefix_visibility' => 'disabled'
                ]);
                break;
            case AppConfigKeys::CAMPAIGN:
                $new_data = array_merge($new_data, [
                    'allow_tribute' => false,
                    'allow_fund' => false,
                    'allow_comments' => false,
                    'campaign_update_visibility' => 'public',
                ]);
                break;
            case AppConfigKeys::USER_PERMISSIONS:
                $new_data = array_merge($new_data, [
                    'allow_anonymous_contributions' => false,
                    'allow_contributor_comments' => false
                ]);
                break;
            case AppConfigKeys::PAYMENT:
                $new_data = array_merge($new_data, [
                    'enable_admin_commission' => false,
                    'enable_guest_checkout' => false,
                ]);
                break;
            case AppConfigKeys::PDF_RECEIPT:
                $new_data = array_merge($new_data, [
                    'enable_annual_receipts' => false,
                ]);
                break;
            case AppConfigKeys::SECURITY:
                $new_data = array_merge($new_data, [
                    'is_enabled_email_verification' => false,
                ]);
                break;
            case AppConfigKeys::BRANDING:
                $new_data = array_merge($new_data, [
                    'button_primary_color' => null,
                    'button_hover_color' => null,
                    'button_text_color' => null
                ]);
                break;
            default:
                break;
        }

        $new_data = apply_filters(HookNames::GROWFUND_BEFORE_APP_CONFIG_UPDATE_FILTER, $new_data, $data); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound

        return $new_data;
    }

    public function get_woocommerce_config() {
        if (!Woocommerce::is_active()) {
            return growfund_response()->json([
				'message' => __('WooCommerce is not active', 'growfund'),
				'data' => []
			], Response::NOT_FOUND);
        }

        return growfund_response()->json([
            'message' => __('WooCommerce configuration', 'growfund'),
            'data' => Woocommerce::get_config()
        ], Response::OK);
    }
}
