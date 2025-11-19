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
            echo "<script>window.location.href = '" . esc_js( $redirect_url ) . "';</script>";
            exit;
        }

        wp_redirect($redirect_url); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
        exit;
    }

    public static function redirect_back($data = [])
    {
        $referer = wp_get_referer();
        $redirect_url = add_query_arg($data, $referer);

        if (headers_sent()) {
            echo "<script>window.location.href = '" . esc_js( $redirect_url ) . "';</script>";
            exit;
        }

        wp_safe_redirect($redirect_url);
        exit;
    }
}
