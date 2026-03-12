<?php

namespace Growfund\Supports;

defined( 'ABSPATH' ) || exit;

use Exception;
use Growfund\Constants\HookNames;
use Growfund\Constants\OptionKeys;
use Growfund\Constants\Status\DonationStatus;
use Growfund\Constants\Status\PledgeStatus;
use Growfund\Payments\Constants\PaymentGatewayType;
use Growfund\Payments\DTO\PaymentMethodDTO;
use Growfund\Services\DonationService;
use Growfund\Services\PledgeService;
use Growfund\Supports\Option;
use WC_Product_Simple;

class Woocommerce
{
    const CHECKOUT_PAGE_STATUS = 'publish';

    protected static $product_id = null;

    protected static $is_active = false;
    
    /**
     * @return bool
     */
    public static function is_active()
    {
        if (static::$is_active) {
            return true;
        }

        if (is_plugin_active('woocommerce/woocommerce.php') && static::is_woocommerce_loaded()) {
            static::$is_active = true;

            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public static function is_woocommerce_loaded()
    {
        if (did_action('woocommerce_loaded')) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public static function is_cart_loaded()
    {
        if (!did_action(HookNames::WP_LOADED) || !function_exists('WC') || !WC()->cart) {
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    public static function is_payment_gateways_loaded()
    {
        if (!function_exists('WC') || !WC()->payment_gateways) {
            return false;
        }

        return true;
    }

    /**
     * @return int
     */
    public static function get_growfund_product_id()
    {
        if (!static::is_active()) {
            return 0;
        }

        if (static::$product_id) {
            return (int) static::$product_id;
        }
        
        static::$product_id = Option::get(OptionKeys::WC_PRODUCT_ID, null);

        if (!empty(static::$product_id) && function_exists('wc_get_product')) {
            $product = wc_get_product(static::$product_id);

            if (empty($product) || $product->get_slug() !== static::get_growfund_product_slug()) {
                static::$product_id = 0;
                Option::delete(OptionKeys::WC_PRODUCT_ID);
            }
        }

        return (int) static::$product_id;
    }

    public static function get_growfund_product_slug()
    {
        return 'growfund-internal';
    }

    /**
     * @return \WC_Product|false|null
     */
    public static function get_product_by_slug()
    {
        return wc_get_product(get_page_by_path(static::get_growfund_product_slug(), OBJECT, 'product')->ID);
    }

    /**
     * @return int
     * @throws Exception
     */
    public static function create_growfund_product()
    {
        if (!static::is_active() || !class_exists('WC_Product') || !class_exists('WC_Product_Simple')) {
            return false;
        }

        $product = static::get_product_by_slug();

        if (!$product) {
            $product = new WC_Product_Simple();

            $product->set_slug(static::get_growfund_product_slug());
            $product->set_name(__('Growfund (internal)', 'growfund'));
            $product->set_status('publish');
            $product->set_price(0);
            $product->set_sale_price(0);
            $product->set_regular_price(0);
            $product->set_virtual(true);
            $product->set_manage_stock(false);
            $product->set_stock_status('instock');
            $product->set_catalog_visibility('hidden');
            $product->set_sold_individually(true);
            $product->save();
        }

        static::$product_id = $product->get_id();

        if (static::$product_id) {
            Option::update(OptionKeys::WC_PRODUCT_ID, static::$product_id);
            return static::$product_id;
        }

        throw new Exception(esc_html__('Failed to create Growfund product in WooCommerce.', 'growfund'));
    }


    /**
     * @param int $contribution_id
     * @return bool
     */
    public static function set_cart_item(int $contribution_id)
    {
        if (!static::is_active() || !static::is_cart_loaded()) {
            return false;
        }

        static::empty_cart();

        $product_id = static::get_growfund_product_id();

        if (empty($product_id)) {
            $product_id = static::create_growfund_product();
            static::$product_id = $product_id;
        }

        $quantity = 1;
        $variation_id = 0;
        $variation = [];
        $cart_item_data = [
            growfund_with_prefix('contribution_id') => $contribution_id
        ];

        $is_added = WC()->cart->add_to_cart(
            $product_id,
            $quantity,
            $variation_id,
            $variation,
            $cart_item_data
        );

        return !empty($is_added);
    }

    /**
     * @return void
     */
    public static function empty_cart()
    {
        if (!static::is_active() || !static::is_cart_loaded()) {
            return;
        }

        if (static::has_growfund_product_in_cart()) {
            $contribution_id = static::get_contribution_id_from_cart();

            if (!empty($contribution_id)) {
                static::remove_pending_contribution_from_cart((int) $contribution_id);
            }
        }

        if (static::is_cart_loaded()) {
			WC()->cart->empty_cart();
        }
    }

    public static function remove_pending_contribution_from_cart(int $contribution_id) {
		if (!static::is_active() || !static::is_cart_loaded()) {
            return;
        }

        if (growfund_app()->is_donation_mode()) {
            $donation_service = new DonationService();
            $donation = $donation_service->get_by_id($contribution_id);

            if ($donation->status === DonationStatus::PENDING) {
                $donation_service->delete($contribution_id);
            }

            return;
		}

        $pledge_service = new PledgeService();
        $pledge = $pledge_service->get_by_id($contribution_id);

        if ($pledge->status === PledgeStatus::PENDING) {
            $pledge_service->delete($contribution_id);
        }
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

    /**
     * @param \WC_Cart|null $cart
     * @return bool
     */
	public static function has_growfund_product_in_cart($cart = null)
    {
        if (!static::is_active() || !static::is_cart_loaded()) {
            return false;
        }

        $cart_items = [];

        if (empty($cart)) {
            try {
				$cart_items = static::is_cart_loaded() ? WC()->cart->get_cart() : [];
			} catch (Exception $e) {
				$cart_items = [];
			}
        } else {
			$cart_items = $cart->get_cart();
        }

        if (empty($cart_items)) {
            return false;
        }

        return Arr::make($cart_items)->every(function ($item) {
            $product = $item['data'];
            return $product->get_id() === static::get_growfund_product_id() && $product->get_slug() === static::get_growfund_product_slug();
        });
    }

    /**
     * @param bool|\WC_Order|\WC_Order_Refund|int $order
     * @return bool
     */
    public static function has_growfund_product_in_order($order) 
    {
        if (!static::is_active()) {
            return false;
        }

        if (is_bool($order)) {
            return false;
        }

        if (is_int($order)) {
            $order = function_exists('wc_get_order') ? wc_get_order($order) : null;
        }

        if (empty($order)) {
            return false;
        }

        $items = $order->get_items();

        if (empty($items)) {
            return false;
        }
        
        return Arr::make($items)->every(function ($item) {
            $product = $item->get_product();
            return $product->get_id() === static::get_growfund_product_id() && $product->get_slug() === static::get_growfund_product_slug();
        });
    }

    /**
     * @param bool|\WC_Order|\WC_Order_Refund|int $order
     * @return int
     */
    public static function get_contribution_id_from_order($order) 
    {
        if (!static::is_active()) {
            return 0;
        }

        if (is_bool($order)) {
            return 0;
        }

        if (is_int($order)) {
            $order = function_exists('wc_get_order') ? wc_get_order($order) : null;
        }

        if (empty($order)) {
            return 0;
        }

        if (!static::has_growfund_product_in_order($order)) {
            return 0;
        }

        return (int) $order->get_meta(growfund_with_prefix('contribution_id'));
    }

    /**
     * @param \WC_Cart|null $cart
     * @return int
     */
	public static function get_contribution_id_from_cart($cart = null)
    {
        if (!static::is_active()) {
            return 0;
        }

        $cart_items = [];

        if (empty($cart)) {
            try {
				$cart_items = static::is_cart_loaded() ? WC()->cart->get_cart() : [];
			} catch (Exception $e) {
				$cart_items = [];
			}
        } else {
			$cart_items = $cart->get_cart();
        }

        $cart_item = Arr::make($cart_items)->find(function ($item) {
            return !empty($item[growfund_with_prefix('contribution_id')] ?? 0);
        });

        return (int) ($cart_item[growfund_with_prefix('contribution_id')] ?? 0);
    }

    /**
     * @param bool|\WC_Order|\WC_Order_Refund|int $order
     * @return string
     */
    public static function get_transaction_id_from_order($order) {
        if (!static::is_active()) {
            return '';
        }

        if (is_bool($order)) {
            return '';
        }

        if (is_int($order)) {
            $order = function_exists('wc_get_order') ? wc_get_order($order) : '';
        }

        if (empty($order) || !static::has_growfund_product_in_order($order)) {
            return '';
        }

        return 'wc_' . $order->get_id();
    }

    /**
     * @param int|\WC_Product|null $product
     * @return bool
     */
    public static function is_growfund_product($product) {
        if (!static::is_active()) {
            return false;
        }

        if (empty($product)) {
            return false;
        }

        if (is_int($product)) {
            $product = function_exists('wc_get_product') ? wc_get_product($product) : null;

            if (empty($product)) {
				return false;
			}
        }
        
        return $product->get_id() === static::get_growfund_product_id() && $product->get_slug() === static::get_growfund_product_slug();
    }

    /**
     * @param bool|\WC_Order|\WC_Order_Refund|int $order
     * @return PaymentMethodDTO|null
     */
    public static function get_payment_method_from_order($order) {
        if (!static::is_active()) {
            return null;
        }

		if (is_bool($order)) {
            return null;
        }

        if (is_int($order)) {
            $order = function_exists('wc_get_order') ? wc_get_order($order) : null;
        }

        if (empty($order)) {
            return null;
        }
        
        $payment_method_dto = new PaymentMethodDTO();
        $payment_method_dto->name = $order->get_payment_method();
        $payment_method_dto->label = $order->get_payment_method_title();
        $payment_method_dto->type = Payment::is_woocommerce_manual_payment_method($payment_method_dto->name) 
            ? PaymentGatewayType::MANUAL 
            : PaymentGatewayType::ONLINE;

        return $payment_method_dto;
    }

    /**
     * Check if the woocommerce checkout page is active
     * 
     * @return bool
     */
    public static function has_checkout_page()
    {
        if (!static::is_active() || !function_exists('wc_get_page_id')) {
            return false;
        }

		$woocommerce_checkout_page_id = wc_get_page_id('checkout');

		return $woocommerce_checkout_page_id > 0 &&  get_post_status($woocommerce_checkout_page_id) === static::CHECKOUT_PAGE_STATUS;
	}
}
