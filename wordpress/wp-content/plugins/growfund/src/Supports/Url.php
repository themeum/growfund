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

    public static function redirect($url, $data = [])
    {
        $referer = $url;
        $redirect_url = add_query_arg($data, $referer);

        if (headers_sent()) {
            wp_print_inline_script_tag("window.location.href = '" . esc_url( wp_json_encode($redirect_url) ) . "';");
            exit;
        }

        wp_safe_redirect($redirect_url);
        exit;
    }

    public static function redirect_back($data = [])
    {
        $referer = wp_get_referer();
        $redirect_url = add_query_arg($data, $referer);

        if (headers_sent()) {
            wp_print_inline_script_tag("window.location.href = '" . esc_url( wp_json_encode($redirect_url) ) . "';");
            exit;
        }

        wp_safe_redirect($redirect_url);
        exit;
    }
}
