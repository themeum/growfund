<?php

namespace Growfund\Hooks\Actions;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\HookNames;
use Growfund\Constants\HookTypes;
use Growfund\Constants\UserTypes\Donor;
use Growfund\Hooks\BaseHook;

/**
 * Add capabilities to the fundraiser role
 * Capabilities:
 * - upload_files
 * - ...
 *
 * @since 1.0.0
 */
class DonorCapabilities extends BaseHook
{
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
        $role = get_role(Donor::ROLE);

        if ($role && !$role->has_cap('upload_files')) {
            $role->add_cap('upload_files', true);
        }
    }
}
