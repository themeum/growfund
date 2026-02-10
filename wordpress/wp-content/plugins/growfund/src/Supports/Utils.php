<?php

namespace Growfund\Supports;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\AppConfigKeys;
use Growfund\Constants\OptionKeys;
use Growfund\Core\AppSettings;
use Growfund\Supports\Option;
use Growfund\SiteRouter;

class Utils
{
    /**
     * Calculates the percentage growth from the previous to the current value.
     *
     * @param mixed $current The current value.
     * @param mixed $previous The previous value.
     * @param int $precision The number of decimal places to round to. Defaults to 2.
     * @return float The percentage growth.
     */
    public static function calculate_growth_in_percent($current, $previous, $precision = 2)
    {
        if (!is_numeric($current) || !is_numeric($previous)) {
            return 0.0;
        }

        $current  = ctype_digit((string) $current) ? (int) $current : (float) $current;
        $previous = ctype_digit((string) $previous) ? (int) $previous : (float) $previous;

        if ($previous === 0 || $previous === 0.0) {
            return $current > 0 ? 100 : 0;
        }

        return round((($current - $previous) / $previous) * 100, $precision);
    }

    /**
     * Determines if the application is in donation mode.
     *
     * @return bool The status of donation mode from the application configuration.
     */
    public static function is_donation_mode()
    {
        return (bool) Option::get(AppConfigKeys::IS_DONATION_MODE);
    }

    /**
     * Check if we are inside the growfund application screen or not.
     *
     * @return bool
     */
    public static function is_growfund_app_screen()
    {
        $current_screen = get_current_screen();
        return $current_screen->base === 'toplevel_page_growfund';
    }

    /**
     * Returns the currency from the application configuration.
     *
     * @return string The currency from the application configuration.
     */
    public static function get_currency()
    {
        $currency_and_symbol = growfund_settings(AppSettings::PAYMENT)->get('currency');

        if (!empty($currency_and_symbol)) {
            $currency_and_symbol = explode(':', $currency_and_symbol);
            return $currency_and_symbol[1] ?? 'USD';
        }

        return 'USD';
    }

    /**
     * Returns the success URL from the application configuration.
     *
     * @param string $payment_method The payment method
     * @param string $campaign_id The campaign ID
     * @param string $pledge_uid The pledge UID
     * @return string 
     */
    public static function get_payment_confirm_url($payment_method, $campaign_id, $pledge_uid)
    {
        return site_url('payment/confirm/?payment_method=' . $payment_method . '&campaign_id=' . $campaign_id . '&uid=' . $pledge_uid);
    }

    /**
     * Returns the checkout URL for a campaign.
     * First tries to get from settings, then falls back to default pattern.
     *
     * @param int $campaign_id The campaign ID
     * @return string The checkout URL
     */
    public static function get_checkout_url($campaign_id = null, $reward_id = null)
    {
        $checkout_page = static::get_default_checkout_url();
        $page_id = growfund_settings(AppSettings::PAGES)->get_checkout_page_id();

        if (!empty($page_id)) {
            $checkout_page = get_permalink($page_id);
        }

        if (empty($campaign_id)) {
            return $checkout_page;
        }

        $args = ['campaign_id' => $campaign_id];

        if (!empty($reward_id)) {
            $args['reward_id'] = $reward_id;
        }

        return add_query_arg($args, $checkout_page);
    }

    /**
     * Returns the URL for default checkout page.
     *
     * @return string The URL for checkout page.
     */
    protected static function get_default_checkout_url()
    {
        return site_url('campaign/checkout');
    }

    /**
     * Returns the URL for checkout submission.
     *
     * @return string The URL for checkout submission.
     */
    public static function get_checkout_submit_url()
    {
        return site_url('campaign/checkout');
    }

    /**
     * Generates a UUID.
     *
     * @return string The UUID.
     */
    public static function uuid()
    {
        return wp_generate_uuid4();
    }

    /**
     * Checks if the current post has either the [growfund_campaigns] or [growfund_checkout] shortcode.
     *
     * @return bool True if the post has either shortcode, false otherwise.
     */
    public static function has_campaign_shortcode()
    {
        global $post;

        return isset($post->post_content) && (
            has_shortcode($post->post_content, 'growfund_campaigns') ||
            has_shortcode($post->post_content, 'growfund_checkout') ||
            has_shortcode($post->post_content, 'growfund_campaign_list')
        );
    }
    
    public static function has_auth_shortcode()
    {
        global $post;

        return isset($post->post_content) && (
            has_shortcode($post->post_content, 'growfund_login') ||
            has_shortcode($post->post_content, 'growfund_register')
        );
    }

    /**
     * Checks if the current page is the checkout page.
     *
     * @return bool True if the page is the checkout page, false otherwise.
     */
    public static function is_checkout_page()
    {
        if (growfund_settings(AppSettings::PAYMENT)->get_checkout_page_id() === get_the_ID()) {
            return true;
        }

        return SiteRouter::get_current_route_name() === 'checkout.index';
    }

    /**
     * Checks if the current page is the donation checkout page.
     *
     * @return bool True if the page is the donation checkout page, false otherwise.
     */
    public static function is_donation_checkout_page()
    {
        if (static::is_checkout_page() && growfund_app()->is_donation_mode()) {
            return true;
        }

        return false;
    }

    /**
     * Checks if the current page is the pledge checkout page.
     *
     * @return bool True if the page is the pledge checkout page, false otherwise.
     */
    public static function is_pledge_checkout_page()
    {
        if (static::is_checkout_page() && !growfund_app()->is_donation_mode()) {
            return true;
        }

        return false;
    }

    public static function is_woocommerce_checkout_page()
    {
		if (!Woocommerce::is_active()) {
			return false;
		}

        if (function_exists('is_checkout') && is_checkout() && Woocommerce::has_growfund_product_in_cart()) {
            return true;
        }

        if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {

			// Safely get the request URI.
			$route = wp_unslash(growfund_input_server('REQUEST_URI', ''));  

            if (stripos($route, 'wc/store') !== false && stripos($route, 'checkout') !== false && Woocommerce::has_growfund_product_in_cart()) {
                return true;
            }
		}


        return false;
    }

    /**
     * Checks if the current route is a dashboard route.
     *
     * @return boolean
     */
    public static function is_dashboard_route()
    {
        return in_array(SiteRouter::get_current_route_name(), ['dashboard.fundraiser', 'dashboard.backer', 'dashboard.donor'], true);
    }

    /**
     * Checks if the current route is a public route.
     *
     * @return boolean
     */
    public static function is_public_route()
    {
        return in_array(SiteRouter::get_current_route_name(), ['public'], true);
    }

    public static function is_migration_available_from_crowdfunding()
    {
        if (!is_plugin_active('wp-crowdfunding/wp-crowdfunding.php') || !growfund_user()->is_admin()) {
            return false;
        }

        $is_already_migrated_from_crowdfunding = Option::get(OptionKeys::IS_MIGRATED_FROM_CROWDFUNDING);

        if (filter_var($is_already_migrated_from_crowdfunding, FILTER_VALIDATE_BOOLEAN)) {
            return false;
        }

        return true;
    }

    public static function is_public_page()
    {
        return SiteRouter::get_current_route_name() === 'public';
    }

    public static function public_url() {
        return static::get_site_url('public/#');
    }

    public static function donation_receipt_url($uid) {
        return static::get_site_url("public/#donations/$uid/receipt");
    }

    public static function ecard_url($uid) {
        return static::get_site_url("public/#donations/$uid/ecard");
    }

    public static function pledge_receipt_url($uid) {
        return static::get_site_url("public/#pledges/$uid/receipt");
    }

    public static function get_site_url($path = '') {
        return site_url($path);
    }

    public static function is_react_site() {
        return static::is_dashboard_route() || static::is_public_route();
    }
}
