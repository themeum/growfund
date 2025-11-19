<?php

namespace Growfund\Hooks\Actions\Woocommerce;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\HookNames;
use Growfund\Constants\HookTypes;
use Growfund\Hooks\BaseHook;
use Growfund\Services\RewardService;
use Growfund\Supports\Money;
use Growfund\Supports\PriceCalculator;
use Growfund\Supports\Woocommerce;
use WC_Shipping_Rate;

class RewardShipping extends BaseHook
{
    public function get_name()
    {
        return HookNames::WC_PACKAGE_RATES;
    }

    public function get_type()
    {
        return HookTypes::ACTION;
    }

    public function handle(...$args)
    {
        $rates = $args[0];

        $contribution_info_dto = Woocommerce::get_custom_cart_info_from_session();
        $shipping_country = WC()->customer->get_shipping_country();

        if (empty($contribution_info_dto)) {
            return $rates;
        }

        if (empty($contribution_info_dto->reward_id) || empty($shipping_country)) {
            return $rates;
        }

        $reward_shipping = new WC_Shipping_Rate();

        $reward_shipping->set_id('reward_shipping:' . $contribution_info_dto->reward_id);
        $reward_shipping->set_method_id('reward_shipping');
        $reward_shipping->set_label('Reward Shipping');

        $reward_shipping->set_cost($this->get_shipping_cost($shipping_country, $contribution_info_dto->reward_id));

        return [$reward_shipping->get_id() => $reward_shipping];
    }

    protected function get_shipping_cost($country, $reward_id)
    {
        $reward_service = new RewardService();

        $reward_dto = $reward_id ? $reward_service->get_by_id($reward_id) : null;

        if (empty($country) || empty($reward_dto)) {
            return 0;
        }

        $cost = PriceCalculator::calculate_shipping_cost(['country' => $country], $reward_dto);

        return $cost > 0 ? Money::prepare_for_display($cost) : 0;
    }
}
