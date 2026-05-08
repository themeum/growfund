<?php

namespace Growfund\Hooks\Actions;

defined( 'ABSPATH' ) || exit;
use Growfund\Constants\HookNames;
use Growfund\Constants\HookTypes;
use Growfund\Core\AssetHandler;
use Growfund\Hooks\BaseHook;
use Growfund\Supports\Location;
use Growfund\Supports\Utils;
use Growfund\Views\Admin\UserFormExtended;

class NewUserForm extends BaseHook
{
    public function get_name()
    {
        return HookNames::WP_NEW_USER_FORM;
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

        $form_key = $args[0]; 

        if (empty($form_key) || $form_key !== 'add-new-user') {
            return;
        }

        $view = new UserFormExtended();
        $view->growfund_roles = Utils::get_growfund_roles();
        $view->growfund_countries = Location::get_countries_for_dropdown();

        growfund_render($view);
        growfund_app(AssetHandler::class)->load_assets();
    }
}
