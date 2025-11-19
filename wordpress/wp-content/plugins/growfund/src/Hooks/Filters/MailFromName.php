<?php

namespace Growfund\Hooks\Filters;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\HookNames;
use Growfund\Constants\HookTypes;
use Growfund\Hooks\BaseHook;

class MailFromName extends BaseHook
{
    public function get_name()
    {
        return HookNames::WP_MAIL_FROM_NAME;
    }

    public function get_type()
    {
        return HookTypes::FILTER;
    }

    public function handle(...$args)
    {
        return get_bloginfo('name');
    }
}
