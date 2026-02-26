<?php

namespace Growfund\Supports;

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
            return growfund_is_valid_url($redirect_url) ? $redirect_url : $url;
        }, 1, 1);
    }
}
