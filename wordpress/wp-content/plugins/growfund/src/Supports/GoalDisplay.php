<?php

namespace Growfund\Supports;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\Campaign\GoalType;

/**
 * Class GoalDisplay
 * 
 * Handles formatting of goal display based on goal type
 * 
 * @since 1.0.0
 */
class GoalDisplay
{
    /**
     * Format goal display based on goal type.
     *
     * @param string|null $goal_type
     * @param int|float|null $goal_amount
     * @param bool $is_donation_mode
     * @return string
     */
    public static function format_goal($goal_type, $goal_amount, $is_donation_mode = false)
    {
        if (empty($goal_type) || empty($goal_amount)) {
            return '';
        }

        switch ($goal_type) {
            case GoalType::RAISED_AMOUNT:
                return growfund_to_currency($goal_amount);

            case GoalType::NO_OF_CONTRIBUTIONS:
                $label = $is_donation_mode
                    ? _n('donation', 'donations', (int) $goal_amount, 'growfund')
                    : _n('pledge', 'pledges', (int) $goal_amount, 'growfund');
                return sprintf('%d %s', (int) $goal_amount, $label);

            case GoalType::NO_OF_CONTRIBUTORS:
                $label = $is_donation_mode
                    ? _n('donor', 'donors', (int) $goal_amount, 'growfund')
                    : _n('backer', 'backers', (int) $goal_amount, 'growfund');
                return sprintf('%d %s', (int) $goal_amount, $label);

            default:
                return (string) $goal_amount;
        }
    }

    /**
     * Format contributors display based on goal type.
     *
     * @param bool $has_goal
     * @param string|null $goal_type
     * @param int|null $number_of_contributors
     * @param int|null $number_of_contributions
     * @return array{count: int, label: string}
     */
    public static function format_contributors_display($has_goal, $goal_type, $number_of_contributors = null, $number_of_contributions = null)
    {
        $is_donation_mode = growfund_app()->is_donation_mode();

        if (!$has_goal && empty($goal_type)) {
            $count = $number_of_contributors ?? 0;
            $label = $is_donation_mode
                ? _n('donor has contributed', 'donors have contributed', $count, 'growfund')
                : _n('backer has contributed', 'backers have contributed', $count, 'growfund');

            return [
                'count' => $count,
                'label' => $label
            ];
        }

        switch ($goal_type) {
            case GoalType::RAISED_AMOUNT:
                $count = $number_of_contributors ?? 0;
                $label = $is_donation_mode
                    ? _n('donor has contributed', 'donors have contributed', $count, 'growfund')
                    : _n('backer has contributed', 'backers have contributed', $count, 'growfund');
                break;

            case GoalType::NO_OF_CONTRIBUTIONS:
                $count = $number_of_contributions ?? 0;
                $label = $is_donation_mode
                    ? _n('donation', 'donations', $count, 'growfund')
                    : _n('pledge', 'pledges', $count, 'growfund');
                break;

            case GoalType::NO_OF_CONTRIBUTORS:
                $count = $number_of_contributors ?? 0;
                $label = $is_donation_mode
                    ? _n('donor', 'donors', $count, 'growfund')
                    : _n('backer', 'backers', $count, 'growfund');
                break;

            default:
                $count = $number_of_contributors ?? 0;
                $label = $is_donation_mode
                    ? _n('donor has contributed', 'donors have contributed', $count, 'growfund')
                    : _n('backer has contributed', 'backers have contributed', $count, 'growfund');
        }

        return [
            'count' => $count,
            'label' => $label
        ];
    }

    /**
     * Format funding amount display for campaign cards.
     *
     * @param string|null $goal_type
     * @param string $raised_amount
     * @param int|null $number_of_contributors
     * @param int|null $number_of_contributions
     * @return string
     */
    public static function format_funding_amount_display($goal_type, $raised_amount, $number_of_contributors = null, $number_of_contributions = null)
    {
        if (empty($goal_type)) {
            return $raised_amount . ' ' . __('raised', 'growfund');
        }

        $is_donation_mode = growfund_app()->is_donation_mode();

        switch ($goal_type) {
            case GoalType::RAISED_AMOUNT:
                return $raised_amount . ' ' . __('raised', 'growfund');

            case GoalType::NO_OF_CONTRIBUTIONS:
                $label = $is_donation_mode
                    ? _n('donation', 'donations', (int) ($number_of_contributions ?? 0), 'growfund')
                    : _n('pledge', 'pledges', (int) ($number_of_contributions ?? 0), 'growfund');
                return ($number_of_contributions ?? 0) . ' ' . $label . ' ' . __('made', 'growfund');

            case GoalType::NO_OF_CONTRIBUTORS:
                $label = $is_donation_mode
                    ? _n('donor', 'donors', (int) ($number_of_contributors ?? 0), 'growfund')
                    : _n('backer', 'backers', (int) ($number_of_contributors ?? 0), 'growfund');
                return ($number_of_contributors ?? 0) . ' ' . $label . ' ' . __('have contributed', 'growfund');

            default:
                return $raised_amount . ' ' . __('raised', 'growfund');
        }
    }

    /**
     * Calculate progress percentage based on goal type.
     *
     * @param string|null $goal_type
     * @param int|float|null $goal_amount
     * @param int|float|null $fund_raised
     * @param int|null $number_of_contributors
     * @param int|null $number_of_contributions
     * @return float
     */
    public static function calculate_progress($goal_type, $goal_amount, $fund_raised = 0, $number_of_contributors = 0, $number_of_contributions = 0)
    {
        if (empty($goal_type) || empty($goal_amount) || $goal_amount <= 0) {
            return 0.0;
        }

        switch ($goal_type) {
            case GoalType::RAISED_AMOUNT:
                return $fund_raised > 0 ? min(100, ($fund_raised / $goal_amount * 100)) : 0.0;

            case GoalType::NO_OF_CONTRIBUTIONS:
                return $number_of_contributions > 0 ? min(100, ($number_of_contributions / $goal_amount * 100)) : 0.0;

            case GoalType::NO_OF_CONTRIBUTORS:
                return $number_of_contributors > 0 ? min(100, ($number_of_contributors / $goal_amount * 100)) : 0.0;

            default:
                return $fund_raised > 0 ? min(100, ($fund_raised / $goal_amount * 100)) : 0.0;
        }
    }
}
