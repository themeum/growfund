<?php

namespace Growfund\Supports;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\MetaKeys\Backer;
use Growfund\Constants\PledgeOption;
use Growfund\Constants\Shipping;
use Growfund\DTO\Pledge\CreatePledgeDTO;
use Growfund\DTO\RewardDTO;

/**
 * Class Money
 *
 * General utility for handling money-related operations such as preparing amounts for,
 * storage, display, etc.
 */
class PriceCalculator
{
    /**
     * Calculate the pledge amount
     * 
     * @param CreatePledgeDTO $dto
     * @param RewardDTO $reward_dto
     * 
     * @return int|float
     */
    public static function calculate_pledge_amount(CreatePledgeDTO $pledge_dto, $reward_dto = null)
    {
        if ($pledge_dto->pledge_option === PledgeOption::WITH_REWARDS) {
            return $reward_dto->amount ?? 0;
        }

        return $pledge_dto->amount ?? 0;
    }

    /**
     * Calculate the pledge amount
     * 
     * @param CreatePledgeDTO $dto
     * 
     * @return int|float
     */
    public static function calculate_pledge_bonus_amount(CreatePledgeDTO $pledge_dto)
    {
        if ($pledge_dto->pledge_option === PledgeOption::WITH_REWARDS) {
            return $pledge_dto->bonus_support_amount ?? 0;
        }

        return 0;
    }

    /**
     * Calculate the shipping cost
     * 
     * @param array $backer_shipping_address
     * @param RewardDTO $reward_dto
     * 
     * @return int|float
     */
    public static function calculate_shipping_cost($backer_shipping_address, $reward_dto = null)
    {
        if (empty($reward_dto->shipping_costs) || empty($backer_shipping_address)) {
            return 0;
        }

        $shipping_costs = Arr::make($reward_dto->shipping_costs);

        $estimated_shipping = $shipping_costs->find(function ($shipping) use ($backer_shipping_address) {
            return $shipping['location'] === $backer_shipping_address['country'];
        });

        if (empty($estimated_shipping)) {
            $rest_of_the_world = $shipping_costs->find(function ($shipping) {
                return $shipping['location'] === Shipping::SHIPPING_REST_OF_THE_WORLD;
            });

            return $rest_of_the_world['cost'] ?? 0;
        }

        return $estimated_shipping['cost'] ?? 0;
    }

    /**
     * Calculate the full pledge amount
     * @param int|float $amount
     * @param int|float $bonus_support_amount
     * @return int|float
     */
    public static function calculate_full_pledge_amount($amount = 0, $bonus_support_amount = 0)
    {
        return $amount + $bonus_support_amount;
    }

    /**
     * Calculate the total fee
     * 
     * @param int|float $amount
     * @param int|float $bonus_support_amount
     * @param int|float $shipping_cost
     * @param int|float $recovery_fee
     * @return int|float
     */
    public static function calculate_pledge_total_amount($amount = 0, $bonus_support_amount = 0, $shipping_cost = 0, $recovery_fee = 0)
    {
        return $amount + $shipping_cost + $bonus_support_amount + $recovery_fee;
    }
}
