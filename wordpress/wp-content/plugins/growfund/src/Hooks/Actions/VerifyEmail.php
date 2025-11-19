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
        if (empty($_GET[growfund_with_prefix('verify_email')]) || '1' !== $_GET[growfund_with_prefix('verify_email')]) { // phpcs:ignore
            return;
        }

        $user_id = absint($_GET['uid']); // phpcs:ignore
        User::verify_email($user_id, Sanitizer::apply_rule($_GET['token'], Sanitizer::TEXT)); // phpcs:ignore
    }
}
