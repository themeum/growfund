<?php

namespace Growfund\DTO;

defined( 'ABSPATH' ) || exit;

use Growfund\Sanitizer;

class OnboardingDTO extends DTO
{
    /** @var string */
    public $campaign_mode;

    /** @var string */
    public $payment_mode;

    /** @var string */
    public $base_country;

    /** @var string */
    public $currency;

    public static function validation_rules(): array
    {
        return [
            'campaign_mode' => 'required|in:reward,donation',
            'payment_mode' => growfund_app()->is_woocommerce_installed() ? 'required|in:native,woo-commerce' : 'required|in:native',
            'base_country' => 'required|string',
            'currency' => 'required|string'
        ];
    }

    public static function sanitization_rules(): array
    {
        return [
            'campaign_mode' => Sanitizer::TEXT,
            'payment_mode' => Sanitizer::TEXT,
            'base_country' => Sanitizer::TEXT,
            'currency' => Sanitizer::TEXT,
        ];
    }
}
