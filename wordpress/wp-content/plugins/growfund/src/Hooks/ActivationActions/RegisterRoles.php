<?php

namespace Growfund\Hooks\ActivationActions;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\UserTypes\Admin;
use Growfund\Contracts\Action;
use Growfund\Constants\UserTypes\Backer;
use Growfund\Constants\UserTypes\Donor;

class RegisterRoles implements Action
{
    public function handle()
    {
        add_role(Backer::ROLE, Backer::TITLE, ['read' => true]);
        add_role(Donor::ROLE, Donor::TITLE, ['read' => true]);

        $this->assign_capabilities();
    }

    protected function assign_capabilities()
    {
        $roles = [Admin::ROLE, Donor::ROLE, Backer::ROLE];

        foreach ($roles as $role) {
            $capabilities = growfund_get_all_capabilities($role);

            foreach ($capabilities as $capability) {
                $role_object = get_role($role);

                if ($role_object && !$role_object->has_cap($capability)) {
                    $role_object->add_cap($capability);
                }
            }
        }
    }
}
