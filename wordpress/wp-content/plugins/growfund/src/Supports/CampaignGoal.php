<?php

namespace Growfund\Supports;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\Campaign\GoalType;
use Growfund\DTO\Campaign\CampaignDTO;
use Growfund\DTO\Donation\CreateDonationDTO;
use Growfund\DTO\Pledge\CreatePledgeDTO;
use Growfund\Services\DonationService;
use Growfund\Services\PledgeService;
use InvalidArgumentException;

/**
 * @since 1.0.0
 */
class CampaignGoal
{
    /**
     * @param string|null $goal_type
     * @param int|float|null $goal_amount
     * 
     * @return int
     */
    public static function prepare_goal_for_storage($goal_type, $goal_amount)
    {
        if (empty($goal_type) || empty($goal_amount)) {
            return 0;
        }

        if ($goal_type === GoalType::RAISED_AMOUNT) {
            return Money::prepare_for_storage($goal_amount);
        }

        return (int) $goal_amount;
    }

    /**
     * @param string|null $goal_type
     * @param int|float|null $goal_amount
     * 
     * @return int|float
     */
    public static function prepare_goal_for_display($goal_type, $goal_amount)
    {
        if (empty($goal_type) || empty($goal_amount)) {
            return 0;
        }

        if ($goal_type === GoalType::RAISED_AMOUNT) {
            return Money::prepare_for_display($goal_amount);
        }

        return (int) $goal_amount;
    }

    /**
     * @param CampaignDTO $campaign_dto
     * @param CreatePledgeDTO|CreateDonationDTO $create_dto
     * 
     * @return bool
     */
    public static function is_reached(CampaignDTO $campaign_dto, $create_dto = null)
    {
        if (!$campaign_dto->has_goal) {
            return false;
        }

        if (!empty($create_dto) && !$create_dto instanceof CreatePledgeDTO && !$create_dto instanceof CreateDonationDTO) {
            /* translators: 1: CreatePledgeDTO::class, 2: CreateDonationDTO::class */
            throw new InvalidArgumentException(sprintf(esc_html__('Only supports %1$s or %2$s as second parameter', 'growfund'), CreatePledgeDTO::class, CreateDonationDTO::class));
        }

        if (GoalType::RAISED_AMOUNT === $campaign_dto->goal_type) {
            $fund_raised = $campaign_dto->fund_raised;

            if (!empty($create_dto) && $create_dto instanceof CreatePledgeDTO) {
                $fund_raised += PriceCalculator::calculate_full_pledge_amount($create_dto->amount ?? 0, $create_dto->bonus_support_amount ?? 0);
            }

            if (!empty($create_dto) && $create_dto instanceof CreateDonationDTO) {
                $fund_raised += $create_dto->amount ?? 0;
            }

            return $fund_raised >= $campaign_dto->goal_amount;
        }

        if (GoalType::NO_OF_CONTRIBUTIONS === $campaign_dto->goal_type) {
            $number_of_contributions = $campaign_dto->number_of_contributions;

            if (!empty($create_dto)) {
                ++$number_of_contributions;
            }

            return $number_of_contributions >= $campaign_dto->goal_amount;
        }

        if (GoalType::NO_OF_CONTRIBUTORS === $campaign_dto->goal_type) {
            $number_of_contributors = growfund_app()->is_donation_mode() 
                ? (new DonationService())->get_total_donors_count_for_campaign($campaign_dto->id, null, null, $create_dto->user_id ?? null) 
                : (new PledgeService())->get_total_backers_count_for_campaign($campaign_dto->id, null, null, $create_dto->user_id ?? null);

            if (!empty($create_dto)) {
                ++$number_of_contributors;
            }

            return $number_of_contributors >= $campaign_dto->goal_amount;
        }
    }

    /**
     * @param CampaignDTO $campaign_dto
     * @param @param CreatePledgeDTO|CreateDonationDTO $create_dto
     * 
     * @return float percentage
     */
    public static function goal_achieved_percentage(CampaignDTO $campaign_dto, $create_dto = null)
    {
        if (!$campaign_dto->has_goal || $campaign_dto->goal_amount <= 0) {
            return 0;
        }

        if (!empty($create_dto) && !$create_dto instanceof CreatePledgeDTO && !$create_dto instanceof CreateDonationDTO) {
            /* translators: 1: CreatePledgeDTO::class, 2: CreateDonationDTO::class */
            throw new InvalidArgumentException(sprintf(esc_html__('Only supports %1$s or %2$s as second parameter', 'growfund'), CreatePledgeDTO::class, CreateDonationDTO::class));
        }

        if (GoalType::RAISED_AMOUNT === $campaign_dto->goal_type) {
            $fund_raised = $campaign_dto->fund_raised;

            if (!empty($create_dto) && $create_dto instanceof CreatePledgeDTO) {
                $fund_raised += PriceCalculator::calculate_full_pledge_amount($create_dto->amount ?? 0, $create_dto->bonus_support_amount ?? 0);
            }

            if (!empty($create_dto) && $create_dto instanceof CreateDonationDTO) {
                $fund_raised += $create_dto->amount ?? 0;
            }

            return (float) ($fund_raised / $campaign_dto->goal_amount * 100);
        }

        if (GoalType::NO_OF_CONTRIBUTIONS === $campaign_dto->goal_type) {
            $number_of_contributions = $campaign_dto->number_of_contributions;

            if (!empty($create_dto)) {
                ++$number_of_contributions;
            }

            return (float) ($number_of_contributions / $campaign_dto->goal_amount * 100);
        }

        if (GoalType::NO_OF_CONTRIBUTORS === $campaign_dto->goal_type) {
            $number_of_contributors = growfund_app()->is_donation_mode() 
                ? (new DonationService())->get_total_donors_count_for_campaign($campaign_dto->id, null, null, $create_dto->user_id ?? null) 
                : (new PledgeService())->get_total_backers_count_for_campaign($campaign_dto->id, null, null, $create_dto->user_id ?? null);

            if (!empty($create_dto)) {
                ++$number_of_contributors;
            }

            return (float) ($number_of_contributors / $campaign_dto->goal_amount * 100);
        }
    }

    /**
     * @param CampaignDTO $campaign_dto
     * @param @param CreatePledgeDTO|CreateDonationDTO $create_dto
     * 
     * @return float percentage
     */
    public static function is_half_goal_achieved(CampaignDTO $campaign_dto, $create_dto = null)
    {
        $percentage = static::goal_achieved_percentage($campaign_dto, $create_dto);

        return $percentage >= 50;
    }

    /**
     * @since 1.0.4
     * 
     * get the goal info for display
     * 
     * @param CampaignDTO $campaign_dto
     * 
     * @return string
     */
    public static function get_goal_info_for_display(CampaignDTO $campaign_dto)
    {
        if (!$campaign_dto->has_goal) {
            return '';
        }

        $is_donation_mode = growfund_app()->is_donation_mode();

        switch ($campaign_dto->goal_type) {
            case GoalType::NO_OF_CONTRIBUTIONS:
                return $is_donation_mode
                /* translators: %s: donation number */
                    ? sprintf(_n('%s donation has made', '%s donations have made', $campaign_dto->number_of_contributions, 'growfund'), $campaign_dto->number_of_contributions)
                    /* translators: %s: pledge number */
                    : sprintf(_n('%s pledge has made', '%s pledges have made', $campaign_dto->number_of_contributions, 'growfund'), $campaign_dto->number_of_contributions);
            case GoalType::NO_OF_CONTRIBUTORS:
                return $is_donation_mode
                    /* translators: %s: donor count */
                    ? sprintf(_n('%s donor has contributed', '%s donors have contributed', $campaign_dto->number_of_contributors, 'growfund'), $campaign_dto->number_of_contributors)
                    /* translators: %s: backer count */
                    : sprintf(_n('%s backer has contributed', '%s backers have contributed', $campaign_dto->number_of_contributors, 'growfund'), $campaign_dto->number_of_contributors);
            default:
				/* translators: %s: raised amount */
                return sprintf(__('%s raised', 'growfund'), Currency::format($campaign_dto->fund_raised ?? 0));
        }
    }
}
