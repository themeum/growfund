<?php

namespace Growfund\Hooks\Actions;

defined( 'ABSPATH' ) || exit;
use Growfund\Constants\HookNames;
use Growfund\Constants\HookTypes;
use Growfund\Core\AssetHandler;
use Growfund\Hooks\BaseHook;
use Growfund\Supports\Location;
use Growfund\Supports\User as UserSupport;
use Growfund\Supports\Utils;
use Growfund\Views\Admin\UserFormExtended;

class EditUserAdminView extends BaseHook
{
    public function get_name()
    {
        return HookNames::WP_EDIT_USER_FORM;
    }

    public function get_type()
    {
        return HookTypes::ACTION;
    }

    public function handle(...$args)
    {
        if (empty($args)) {
            return;
        }

        /** @var \WP_User */
        $user = $args[0];

        $view = new UserFormExtended();

        $view->user_roles = UserSupport::get_roles($user);
        $view->is_billing_address_same = UserSupport::is_billing_address_same($user->ID);
        $view->billing_address = UserSupport::get_billing_address($user->ID);
        $view->shipping_address = UserSupport::get_shipping_address($user->ID);
        $view->phone = UserSupport::get_phone_number($user->ID);
        $view->is_verified = UserSupport::is_verified($user);

        $view->growfund_roles = Utils::get_growfund_roles();
        $view->growfund_countries = Location::get_countries_for_dropdown();

        growfund_render($view);
        growfund_app(AssetHandler::class)->load_assets();
    }
}
