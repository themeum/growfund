<?php

namespace Growfund\Hooks\Filters;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\HookNames;
use Growfund\Constants\HookTypes;
use Growfund\Hooks\BaseHook;
use Growfund\Supports\AdminUser;

class MailFromAddress extends BaseHook
{
    public function get_name()
    {
        return HookNames::WP_MAIL_FROM;
    }

    public function get_type()
    {
        return HookTypes::FILTER;
    }

    public function handle(...$args)
    {
        return AdminUser::get_email();
    }
}
