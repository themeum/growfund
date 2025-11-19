<?php

namespace Growfund\Hooks\Filters\Woocommerce;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\HookNames;
use Growfund\Constants\HookTypes;
use Growfund\Core\AppSettings;
use Growfund\Hooks\BaseHook;
use Growfund\Supports\Fund;
use Growfund\Supports\Woocommerce;

class ClassicCustomCheckoutFields extends BaseHook
{
    public function get_name()
    {
        return HookNames::WC_CLASSIC_CHECKOUT_FIELDS;
    }

    public function get_type()
    {
        return HookTypes::FILTER;
    }

    public function handle(...$args)
    {
        $fields = $args[0];

        if (!Woocommerce::is_native_checkout()) {
            return $fields;
        }

        if (growfund_app()->is_donation_mode()) {
            return $this->register_donation_fields($fields);
        }

        return $this->register_pledge_fields($fields);
    }

    protected function register_pledge_fields($fields)
    {
        $fields['order'][growfund_with_prefix('bonus_support_amount')] = [
            'type'        => 'text',
            'label'       => __('Bonus Support Amount', 'growfund'),
            'placeholder' => __('Enter an amount', 'growfund'),
            'required'    => false,
            'class'       => ['form-row-wide'],
            'validate'    => ['number'],
        ];

        return $fields;
    }

    protected function register_donation_fields($fields)
    {
        $contribution_info_dto = Woocommerce::get_custom_cart_info_from_session();
        $funds = Fund::get_funds_for_donation($contribution_info_dto->campaign_id);

        if (!empty($funds)) {
            $fund_options = [];

            foreach ($funds as $fund) {
                $fund_options[$fund['id']] = $fund['title'];
            }

            $fields['order'][growfund_with_prefix('fund_id')] = [
                'type'        => 'select',
                'label'       => 'Which fund would you like to support?',
                'placeholder' => 'Select a fund',
                'required'    => true,
                'class'       => ['form-row-wide'],
                'options'     => $fund_options,
            ];
        }

        $fields['order'][growfund_with_prefix('donation_amount')] = [
            'type'        => 'text',
            'label'       => __('Donation Amount', 'growfund'),
            'placeholder' => __('Enter an amount', 'growfund'),
            'required'    => true,
            'class'       => ['form-row-wide'],
            'validate'    => ['number'],
        ];

        if (growfund_app()->is_donation_mode() && growfund_settings(AppSettings::PERMISSIONS)->allow_anonymous_donation()) {
            $fields['order'][growfund_with_prefix('is_anonymous')] = [
                'type'        => 'checkbox',
                'label'       => __("Don't display my name publicly on the fundraiser", 'growfund'),
                'required'    => false,
                'class'       => ['form-row-wide'],
            ];
        }

        return $fields;
    }
}
