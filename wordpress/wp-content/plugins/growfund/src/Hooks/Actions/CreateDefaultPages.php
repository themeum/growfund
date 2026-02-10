<?php

namespace Growfund\Hooks\Actions;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\HookNames;
use Growfund\Constants\HookTypes;
use Growfund\Hooks\BaseHook;
use Growfund\Services\PageService;

class CreateDefaultPages extends BaseHook
{
    public function get_name()
    {
        return HookNames::ONBOARDING_COMPLETED;
    }

    public function get_type()
    {
        return HookTypes::ACTION;
    }

    public function handle(...$args)
    {
		$page_service = new PageService();
		$page_service->generate_growfund_pages();
    }
}
