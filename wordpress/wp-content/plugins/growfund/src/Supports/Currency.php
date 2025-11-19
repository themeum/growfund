<?php

namespace Growfund\Supports;

defined( 'ABSPATH' ) || exit;

use Growfund\Core\CurrencyConfig;
use InvalidArgumentException;

class Currency
{
    /**
     * Format a currency amount according to the currency settings.
     *
     * @param int|float $amount
     * @return string
     */
    public static function format($amount)
    {
        if (!is_numeric($amount)) {
            throw new InvalidArgumentException(esc_html__('Invalid number provided as currency value.', 'growfund'));
        }

        $currency_settings = growfund_app()->make(CurrencyConfig::class)->get();

        $formatted_amount = number_format(
            $amount,
            $currency_settings['decimal_places'],
            $currency_settings['decimal_separator'],
            $currency_settings['thousand_separator']
        );

        $currency_parts = explode(':', $currency_settings['currency']);
        $currency_symbol = $currency_parts[0] ?? '$';

        return $currency_settings['currency_position'] === 'after'
            ? sprintf('%s%s', $formatted_amount, $currency_symbol)
            : sprintf('%s%s', $currency_symbol, $formatted_amount);
    }
}
