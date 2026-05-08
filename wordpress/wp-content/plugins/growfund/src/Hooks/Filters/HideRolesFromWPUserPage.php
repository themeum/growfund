<?php

namespace Growfund\Hooks\Filters;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\HookNames;
use Growfund\Constants\HookTypes;
use Growfund\Constants\UserTypes\Backer;
use Growfund\Constants\UserTypes\Collaborator;
use Growfund\Constants\UserTypes\Donor;
use Growfund\Constants\UserTypes\Fundraiser;
use Growfund\Hooks\BaseHook;

class HideRolesFromWPUserPage extends BaseHook
{
    public function get_name()
    {
        return HookNames::EDITABLE_ROLES;
    }

    public function get_type()
    {
        return HookTypes::FILTER;
    }

    public function handle(...$args)
    {
        $roles = $args[0];

        $hidden_roles = [
            Fundraiser::ROLE,
            Collaborator::ROLE,
            Backer::ROLE,
            Donor::ROLE,
        ];

        foreach ($hidden_roles as $role) {
            if (isset($roles[$role])) {
                unset($roles[$role]);
            }
        }

        return $roles;
    }
}
