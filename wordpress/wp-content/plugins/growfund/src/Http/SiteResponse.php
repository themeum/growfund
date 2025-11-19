<?php

namespace Growfund\Http;

defined( 'ABSPATH' ) || exit;

/**
 * Site Response Class
 * 
 * Handles both HTML and JSON responses for site requests.
 * Provides a unified interface for different response types.
 * 
 * @since 1.0.0
 */
class SiteResponse
{
    /**
     * Send a JSON success response
     *
     * @param array $data Response data
     * @param int $status_code HTTP status code
     * @return void
     */
    public function json($data = [], $status_code = 200)
    {
        wp_send_json_success($data, $status_code);
    }





    /**
     * Send a plain HTML response (for non-AJAX requests)
     *
     * @param string $html HTML content
     * @param int $status_code HTTP status code
     * @return void
     */
    public function send_html($html, $status_code = 200)
    {
        if ($status_code !== 200) {
            http_response_code($status_code);
        }
        echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped  -- output is already escaped. this is intentional
        exit;
    }

    /**
     * Send a redirect response
     *
     * @param string $url Redirect URL
     * @param int $status_code HTTP status code (301 or 302)
     * @return void
     */
    public function redirect($url, $status_code = 302)
    {
        wp_redirect($url, $status_code); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
        exit;
    }



    /**
     * Legacy method for backward compatibility
     *
     * @param array $data Response data
     * @param int $status_code HTTP status code
     * @return void
     */
    public function success($data = [], $status_code = 200)
    {
        $this->json($data, $status_code);
    }

    /**
     * Send a JSON error response
     *
     * @param array $data Error data
     * @param int $status_code HTTP status code
     * @return void
     */
    public function json_error($data = [], $status_code = 400)
    {
        wp_send_json_error($data, $status_code);
    }

    /**
     * Legacy method for backward compatibility
     *
     * @param array $data Error data
     * @param int $status_code HTTP status code
     * @return void
     */
    public function error($data = [], $status_code = 400)
    {
        $this->json_error($data, $status_code);
    }
}
