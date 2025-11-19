<?php

namespace Growfund\Hooks\Actions;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\HookNames;
use Growfund\Constants\HookTypes;
use Growfund\Hooks\BaseHook;

/**
 * Class AuthRedirect
 * 
 * Redirects WordPress default login page to custom growfund login page
 * 
 * @since 1.0.0
 */
class AuthRedirect extends BaseHook
{
    /**
     * Get the WordPress hook name this subscriber listens to.
     *
     * @return string The hook name
     */
    public function get_name()
    {
        return HookNames::INIT;
    }

    /**
     * Get the type of the hook: 'action' or 'filter'.
     *
     * @return string Either 'action' or 'filter'
     */
    public function get_type()
    {
        return HookTypes::ACTION;
    }

    /**
     * Handle the hook logic.
     * Redirects WordPress login page to custom growfund login page.
     *
     * @param mixed ...$args All arguments passed to the hook
     * @return void
     */
    public function handle(...$args)
    {
        $referer = wp_get_referer();

        if (strpos($referer, 'wp-admin') !== true) {
            return;
        }

        if (is_login() && !is_user_logged_in()) {
            $redirect_to = get_query_var('redirect_to');

            $custom_login_url = growfund_login_url();

            if (!empty($redirect_to)) {
                $custom_login_url = add_query_arg('redirect_to', urlencode($redirect_to), $custom_login_url);
            }

            return growfund_redirect($custom_login_url);
        }
    }
}
