<?php

namespace Growfund\Hooks\Actions;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\AppConfigKeys;
use Growfund\Constants\HookNames;
use Growfund\Constants\HookTypes;
use Growfund\Constants\TransientKeys;
use Growfund\Hooks\BaseHook;
use Growfund\Supports\Option;

class ManageOnboarding extends BaseHook
{
    public function get_name()
    {
        return HookNames::ADMIN_INIT;
    }

    public function get_type()
    {
        return HookTypes::ACTION;
    }

    public function handle(...$args)
    {
        if (get_transient(TransientKeys::GROWFUND_GROWFUND_ACTIVATED)) {
            delete_transient(TransientKeys::GROWFUND_GROWFUND_ACTIVATED);

            $is_onboarding_completed = Option::get(AppConfigKeys::IS_ONBOARDING_COMPLETED);

            if (!$is_onboarding_completed) {
                wp_safe_redirect(admin_url('admin.php?page=growfund#/onboarding'));
                exit;
            }
        }
    }
}
