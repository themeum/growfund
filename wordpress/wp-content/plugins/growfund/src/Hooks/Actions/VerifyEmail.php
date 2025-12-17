<?php

namespace Growfund\Hooks\Actions;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\HookNames;
use Growfund\Constants\HookTypes;
use Growfund\Hooks\BaseHook;
use Growfund\Sanitizer;
use Growfund\Supports\User;

class VerifyEmail extends BaseHook
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
        $input = growfund_input_get(growfund_with_prefix('verify_email'));
        if (empty($input) || '1' !== $input) {
            return;
        }

        $user_id = absint(growfund_input_get('uid'));
        User::verify_email($user_id, Sanitizer::apply_rule(growfund_input_get('token'), Sanitizer::TEXT));
    }
}
