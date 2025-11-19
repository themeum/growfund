<?php

namespace Growfund\Middlewares\Site;

defined( 'ABSPATH' ) || exit;

use Growfund\Contracts\Middleware;
use Growfund\Contracts\Request;

/**
 * Middleware to ensure the user is authenticated.
 *
 * Blocks access to routes unless the user is logged in.
 *
 * @since 1.0.0
 */
class FundraiserMiddleware implements Middleware
{
    /**
     * Handle the incoming request and determine if the user is authenticated.
     *
     * @since 1.0.0
     *
     * @param Request $request The incoming request instance.
     * @param callable $next The next middleware in the chain.
     * @return mixed The result of the next middleware or a redirect response.
     */
    public function handle(Request $request, callable $next)
    {
        if (is_user_logged_in() && growfund_user()->is_fundraiser()) {
            return $next($request);
        }

        growfund_redirect(site_url());
    }
}
