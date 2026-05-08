<?php

namespace Growfund\Hooks\Actions;

use Growfund\Constants\HookNames;
use Growfund\Constants\HookTypes;
use Growfund\Constants\UserTypes\Collaborator;
use Growfund\Hooks\BaseHook;
use Growfund\Supports\User as UserSupport;

defined( 'ABSPATH' ) || exit;

class RolesInit extends BaseHook {
    public function get_name()
    {
        return HookNames::INIT;
    }

    public function get_type()
    {
        return HookTypes::ACTION;
    }

    public function handle(...$args)
    {
        $role = get_role(Collaborator::ROLE);

        if (!$role) {
            UserSupport::register_role(Collaborator::ROLE, Collaborator::TITLE);
        }
    }
}
