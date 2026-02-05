<?php

namespace Growfund\Hooks\Actions;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\HookNames;
use Growfund\Constants\HookTypes;
use Growfund\Hooks\BaseHook;
use Growfund\Route;

class RegisterRestApi extends BaseHook
{
    public function get_name()
    {
        return HookNames::REST_API_INIT;
    }

    public function get_type()
    {
        return HookTypes::ACTION;
    }

    public function handle(...$args)
    {
        do_action(HookNames::GROWFUND_ROUTE_BEFORE_INIT_ACTION); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound

        $routes = Route::get_routes();

        foreach ($routes as $route) {
            $route->register();
        }
    }
}
