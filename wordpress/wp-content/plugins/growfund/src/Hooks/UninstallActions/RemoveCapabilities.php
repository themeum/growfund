<?php

namespace Growfund\Hooks\UninstallActions;

defined( 'ABSPATH' ) || exit;

use Growfund\Contracts\Action;

class RemoveCapabilities implements Action
{
    public function handle()
    {
        global $wp_roles;

        if (! isset($wp_roles)) {
            $wp_roles = wp_roles(); // phpcs:ignore -- intentionally used
        }

        foreach ($wp_roles->roles as $role_name => $role_info) {
            $role = get_role($role_name);

            if (! $role) {
                continue;
            }

            foreach (growfund_get_all_capabilities() as $capability) {
                $role->remove_cap($capability);
            }
        }
    }
}
