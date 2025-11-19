<?php

namespace Growfund\Supports;

defined( 'ABSPATH' ) || exit;

use Automattic\WooCommerce\StoreApi\Utilities\CartController;
use Growfund\Constants\OptionKeys;
use Growfund\DTO\Woocommerce\WoocommerceContributionDTO;
use Growfund\Supports\Option;
use WC_Product_Simple;

class Woocommerce
{
    public static function is_active()
    {
        if (is_plugin_active('woocommerce/woocommerce.php')) {
            return true;
        }

        return false;
    }
    public static function get_growfund_product_id()
    {
        return (int) Option::get(OptionKeys::WC_PRODUCT_ID) ?? null;
    }

    public static function create_growfund_product()
    {
        if (!empty(Option::get(OptionKeys::WC_PRODUCT_ID))) {
            return;
        }

        $product_name = 'Growfund (internal)';
        $price = 1000;

        if (!class_exists('WC_Product')) {
            return;
        }

        $product = new WC_Product_Simple();

        $product->set_name($product_name);
        $product->set_status('publish');
        $product->set_catalog_visibility('hidden');
        $product->set_price($price);
        $product->set_regular_price($price);
        $product->set_manage_stock(false);
        $product->set_stock_status('instock');

        $product_id = $product->save();

        if ($product_id) {
            Option::update(OptionKeys::WC_PRODUCT_ID, $product_id);
        } else {
            error_log('Failed to create CF product.'); // phpcs:ignore
        }
    }

    /**
     * Summary of get_custom_cart_info_from_session
     * @return WoocommerceContributionDTO|null
     */
    public static function get_custom_cart_info_from_session()
    {
        if (!isset(WC()->session)) {
            return null;
        }

        $contribution_info = WC()->session->get(growfund_with_prefix('contribution_info'));

        if (empty($contribution_info)) {
            return null;
        }

        return WoocommerceContributionDTO::from_array($contribution_info);
    }

    public static function set_custom_cart_info_to_session(WoocommerceContributionDTO $contribution_info_dto)
    {
        WC()->session->set(growfund_with_prefix('contribution_info'), $contribution_info_dto->to_array());
    }

    public static function unset_custom_cart_info_from_session()
    {
        WC()->session->__unset(growfund_with_prefix('contribution_info'));
    }

    public static function is_native_checkout()
    {
        try {
            $cart_controller = new CartController();
            $cart = $cart_controller->get_cart_instance();

            foreach ($cart->get_cart() as $item) {
                if ($item['product_id'] === static::get_growfund_product_id()) {
                    return true;
                }
            }
        } catch (\Exception $e) {
            return false;
        }

        return false;
    }

    public static function get_config() {
        if (!static::is_active()) {
            return [];
        }

        list($country_code) = explode(':', Option::get('woocommerce_default_country', 'US'));
        $currency_code = get_woocommerce_currency();
		$currency_symbol = get_woocommerce_currency_symbol($currency_code);
        $currency_position = Option::get('woocommerce_currency_pos', 'left');

        return [
            'country' => $country_code,
            'currency' => $currency_symbol . ':' . $currency_code,
            "currency_position" => $currency_position === 'left' ? 'before' : 'after',
            "decimal_separator" => wc_get_price_decimal_separator(),
            "thousand_separator" => wc_get_price_thousand_separator(),
            "decimal_places" => wc_get_price_decimals(),
        ];
    }
}
