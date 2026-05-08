<?php

namespace Growfund\Hooks\ActivationActions;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\UserTypes\Admin;
use Growfund\Contracts\Action;
use Growfund\Constants\UserTypes\Backer;
use Growfund\Constants\UserTypes\Donor;
use Growfund\Supports\User as UserSupport;

class RegisterRoles implements Action
{
    public function handle()
    {
        UserSupport::register_role(Backer::ROLE, Backer::TITLE);
        UserSupport::register_role(Donor::ROLE, Donor::TITLE);

        $this->assign_admin_capabilities();
    }

    protected function assign_admin_capabilities()
    {
        $capabilities = growfund_get_all_capabilities(Admin::ROLE);

        foreach ($capabilities as $capability) {
            $role_object = get_role(Admin::ROLE);

            if ($role_object && !$role_object->has_cap($capability)) {
                $role_object->add_cap($capability);
            }
        }
    }
}
