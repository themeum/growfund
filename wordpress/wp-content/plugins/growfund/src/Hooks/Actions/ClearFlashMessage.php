<?php

namespace Growfund\Hooks\Actions;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\HookNames;
use Growfund\Constants\HookTypes;
use Growfund\Hooks\BaseHook;
use Growfund\Supports\FlashMessage;

class ClearFlashMessage extends BaseHook
{
    public function get_name()
    {
        return HookNames::INIT;
    }

    public function get_type()
    {
        return HookTypes::ACTION;
    }

    public function get_priority()
    {
        return 1;
    }

    public function handle(...$args)
    {
        FlashMessage::flush();
    }
}
