<?php

namespace Growfund\Supports;

use Growfund\Core\AppSettings;
use Growfund\Payments\Constants\PaymentGatewayType;

defined( 'ABSPATH' ) || exit;

class Url
{
    public static function make($url, $data = [])
    {
        $referer = $url;
        return add_query_arg($data, $referer);
    }

    public static function redirect($url, $data = [], $status = 302)
    {
        $referer = $url;
        $redirect_url = add_query_arg($data, $referer);

        static::filter_safe_url($redirect_url);

        if (headers_sent()) {
            wp_print_inline_script_tag("window.location.href = '" . esc_url( wp_json_encode($redirect_url) ) . "';");
            exit;
        }

        wp_safe_redirect($redirect_url, $status);
        exit;
    }

    public static function redirect_back($query_params = [])
    {
        $redirect_url = static::get_previous_url($query_params);

        static::filter_safe_url($redirect_url);

        if (headers_sent()) {
            wp_print_inline_script_tag("window.location.href = '" . esc_url( wp_json_encode($redirect_url) ) . "';");
            exit;
        }

        wp_safe_redirect($redirect_url);
        exit;
    }

    public static function get_previous_url(array $query_params = [])
    {
        $referer = wp_get_referer();
        $redirect_url = add_query_arg($query_params, $referer);

        return $redirect_url;
    }
    
    protected static function filter_safe_url($redirect_url)
    {
        add_filter('wp_safe_redirect_fallback', function ($url) use ($redirect_url) {
            if (rtrim($url, '/') !== rtrim($redirect_url)) {
                $payments = growfund_settings(AppSettings::PAYMENT)->get('payments', []);

				$is_safe_url = Arr::make($payments)
                    ->filter(function ($item) {
                        return $item['type'] === PaymentGatewayType::ONLINE;
                    })->some(function ($item) use ($redirect_url) {
                        $domain = $item['config']['domain_name'] ?? strtolower($item['config']['label'] ?? '');
                        return !empty($domain) && strpos($redirect_url, $domain) !== false;
                    });

				// for payment gateways allowed safe redirect
				if ($is_safe_url) {
					return $redirect_url;
				}
            }

            return $url;
        }, 1, 1);
    }
}
