<?php

namespace Growfund\Hooks\Actions;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\HookNames;
use Growfund\Constants\HookTypes;
use Growfund\Hooks\BaseHook;
use Growfund\Managers\VersionManager;
class PluginUpdateAction extends BaseHook
{
    public function get_name()
    {
        return HookNames::ADMIN_INIT;
    }

    public function get_type()
    {
        return HookTypes::ACTION;
    }

    public function get_priority()
    {
        return 5;
    }

    public function handle(...$args)
    {
        $version_manager = VersionManager::get_instance();
        $version_manager->run();
    }
}
