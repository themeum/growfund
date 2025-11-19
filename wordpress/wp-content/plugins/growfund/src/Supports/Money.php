<?php

namespace Growfund\Supports;

defined( 'ABSPATH' ) || exit;

use Growfund\Core\AppSettings;

/**
 * Class Money
 *
 * General utility for handling money-related operations such as preparing amounts for,
 * storage, display, etc.
 */
class Money
{
    /**
     * Prepare a real amount (e.g. 12.50) to integer (e.g. 1250) for storage.
     *
     * @param float|int|string $amount The real amount.
     * @return int The normalized integer value.
     */
    public static function prepare_for_storage($amount)
    {
        return (int) round(floatval($amount) * 100);
    }

    /**
     * Prepare an integer amount (e.g. 1250) to real value (e.g. 12.50) for display/use.
     *
     * @param int|float $amount The integer amount (multiplied by 100).
     * @return float The real value.
     */
    public static function prepare_for_display($amount)
    {
        return (float) ($amount / 100);
    }

    /**
     * Calculate the recovery fee based on the amount, bonus support amount, and shipping cost.
     *
     * @param int $amount Amount in cents
     * @param int $bonus_support_amount Amount in cents
     * @param int $shipping_cost Amount in cents
     * @return int
     */
    public static function calculate_recovery_fee($amount, $bonus_support_amount = 0, $shipping_cost = 0)
    {
        $payment_options = growfund_settings(AppSettings::PAYMENT)->get();

        if (empty($payment_options)) {
            return 0;
        }

        if (empty($payment_options['enable_fee_recovery'])) {
            return 0;
        }

        $total = $amount + $bonus_support_amount + $shipping_cost;

        $recovery_fee_percent = $payment_options['fee_percentage'] ?? 0;
        $recovery_fee_flat = static::prepare_for_storage($payment_options['fee_fixed_amount']) ?? 0;
        $max_fee_amount = static::prepare_for_storage($payment_options['max_fee_amount']) ?? null;

        $fee_amount = (int) ($total * $recovery_fee_percent / 100) + $recovery_fee_flat;

        return !empty($max_fee_amount) ? min($fee_amount, $max_fee_amount) : $fee_amount;
    }
}
