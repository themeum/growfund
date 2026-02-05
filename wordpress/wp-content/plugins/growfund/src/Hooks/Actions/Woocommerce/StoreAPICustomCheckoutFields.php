<?php

namespace Growfund\Hooks\Actions\Woocommerce;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\HookNames;
use Growfund\Constants\HookTypes;
use Growfund\Core\AppSettings;
use Growfund\Hooks\BaseHook;
use Growfund\Supports\Arr;
use Growfund\Supports\Fund;
use Growfund\Supports\Woocommerce;

class StoreAPICustomCheckoutFields extends BaseHook
{
    public function get_name()
    {
        return HookNames::WP;
    }

    public function get_type()
    {
        return HookTypes::ACTION;
    }

    public function handle(...$args)
    {
        if (!Woocommerce::is_active() || !Woocommerce::is_native_checkout()) {
            return;
        }

        if (growfund_app()->is_donation_mode()) {
            $this->register_donation_fields();
        } else {
            $this->register_pledge_fields();
        }
    }

    protected function register_pledge_fields()
    {
        woocommerce_register_additional_checkout_field(
            [
                'id'            => 'growfund/bonus_support_amount',
                'label'         => __('Bonus Support Amount', 'growfund'),
                'optionalLabel' => __('Bonus Support Amount', 'growfund'),
                'location'      => 'order',
                'required'      => false,
                'attributes'    => [
                    'pattern'          => '^\d+(\.\d{1,2})?$',
                    'title'            => __('Enter an amount', 'growfund'),
                ]
            ]
        );
    }

    protected function register_donation_fields()
    {
        $contribution_info_dto = Woocommerce::get_custom_cart_info_from_session();
        
        if (!$contribution_info_dto) {
            return;
        }

        $funds = Fund::get_funds_for_donation($contribution_info_dto->campaign_id);

        if (!empty($funds)) {
            $fund_options = Arr::make($funds)->map(function ($fund) {
                return [
                    'value' => $fund->id,
                    'label' => $fund->title
                ];
            })->toArray();

            woocommerce_register_additional_checkout_field(
                [
                    'id'          => 'growfund/fund_id',
                    'label'       => 'Which fund would you like to support?',
                    'placeholder' => 'Select a fund',
                    'location'    => 'order',
                    'type'        => 'select',
                    'required'    => true,
                    'options'     => $fund_options
                ]
            );
        }

        woocommerce_register_additional_checkout_field(
            [
                'id'            => 'growfund/donation_amount',
                'label'         => __('Donation Amount', 'growfund'),
                'optionalLabel' => __('Donation Amount', 'growfund'),
                'location'      => 'order',
                'required'      => false,
                'attributes'    => [
                    'pattern'          => '^\d+(\.\d{1,2})?$',
                    'title'            => __('Enter an amount', 'growfund'),
                ]
            ]
        );

        if (growfund_app()->is_donation_mode() && growfund_settings(AppSettings::PERMISSIONS)->allow_anonymous_donation()) {
            woocommerce_register_additional_checkout_field(
                [
                    'id'       => 'growfund/is_anonymous',
                    'label'    => "Don't display my name publicly on the fundraiser.",
                    'location' => 'order',
                    'type'     => 'checkbox',
                ]
            );
        }
    }
}
