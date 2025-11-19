<?php

namespace Growfund\Core;

defined( 'ABSPATH' ) || exit;

use Growfund\Supports\Option;
use Growfund\Supports\Arr;
use Exception;
use InvalidArgumentException;

class CurrencyConfig
{
    /**
     * The currency configuration.
     *
     * @var array
     */
    protected $config = [];

    /**
     * Initialize the currency configuration.
     */
    public function __construct()
    {
        $this->config = $this->retrieve();
    }

    /**
     * Get the currency configuration.
     *
     * @return array
     */
    public function get()
    {
        return $this->config;
    }

    /**
     * Get the currency symbol for a given currency code.
     *
     * @param string $code
     * @return string
     */
    protected function get_currency_symbol(string $code)
    {
        if (empty($code)) {
            throw new InvalidArgumentException(esc_html__('Currency code is required.', 'growfund'));
        }

        $currency_data_path = GROWFUND_DIR_PATH . 'resources/data/currencies.json';

        if (!file_exists($currency_data_path)) {
            throw new Exception(esc_html__('Currency data file not found.', 'growfund'));
        }

        $currency_data = file_get_contents($currency_data_path);
        $currency_data = json_decode($currency_data, true);

        $currency = Arr::make($currency_data)->find(function ($item) use ($code) {
            return $item['code'] === $code;
        });

        return $currency['symbol'] ?? $code;
    }

    /**
     * Retrieve the currency configuration.
     *
     * @return array
     * @throws Exception|InvalidArgumentException
     */
    protected function retrieve()
    {
        $payment = growfund_settings(AppSettings::PAYMENT);

        if (growfund_app()->is_woocommerce_installed() && $payment->get('e_commerce_engine') === 'woo-commerce') {
            $currency = Option::get('woocommerce_currency', 'USD');
            $symbol = $this->get_currency_symbol($currency);

            $position = Option::get('woocommerce_currency_pos', 'right');
            $position = in_array($position, ['left', 'left_space'], true) ? 'before' : 'after';

            return [
                'currency' => sprintf('%s:%s', $symbol, $currency),
                'currency_position' => $position,
                'thousand_separator' => Option::get('woocommerce_price_thousand_sep', ','),
                'decimal_separator' => Option::get('woocommerce_price_decimal_sep', '.'),
                'decimal_places' => (int) Option::get('woocommerce_price_num_decimals', 2),
            ];
        }

        return [
            'currency' => $payment->get('currency', '$:USD'),
            'currency_position' => $payment->get('currency_position', 'before'),
            'thousand_separator' => $payment->get('thousand_separator', ','),
            'decimal_separator' => $payment->get('decimal_separator', '.'),
            'decimal_places' => $payment->get('decimal_places', 2),
        ];
    }
}
