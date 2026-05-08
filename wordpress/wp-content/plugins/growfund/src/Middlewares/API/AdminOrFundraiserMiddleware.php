<?php

namespace Growfund\Middlewares\API;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\UserTypes\Collaborator;
use Growfund\Constants\UserTypes\Fundraiser;
use Growfund\Contracts\Middleware;
use Growfund\Contracts\Request;
use Growfund\Exceptions\AuthorizationException;

/**
 * Middleware to ensure the user is authenticated.
 *
 * Blocks access to routes unless the user is logged in.
 *
 * @since 1.0.0
 */
class AdminOrFundraiserMiddleware implements Middleware
{
    /**
     * Handle the incoming request and determine if the user is authenticated.
     *
     * @since 1.0.0
     *
     * @param Request $request The incoming request instance.
     * @param callable $next The next middleware in the chain.
     * @return mixed The result of the next middleware or a response.
     */
    public function handle(Request $request, callable $next)
    {
        if (
            growfund_user()->is_logged_in() 
            && (
                growfund_user()->is_admin() 
                || growfund_user()->has_active_role(Fundraiser::ROLE) 
                || growfund_user()->has_active_role(Collaborator::ROLE)
            )
        ) {
            return $next($request);
        }

        throw new AuthorizationException(esc_html__('You do not have permission for this action', 'growfund'));
    }
}
