<?php

namespace Growfund\Hooks\Actions;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\HookNames;
use Growfund\Constants\HookTypes;
use Growfund\Hooks\BaseHook;
use Growfund\Services\AppConfigService;
use Growfund\Services\PageService;
use Growfund\Supports\Option;

class OnboardingCompleted extends BaseHook
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
        $app_config_service = new AppConfigService();
        
        $app_config_service->save_paypal_config();
        Option::store_default_email_templates();
        Option::store_default_pdf_templates();
        $app_config_service->save_salutation();

		$page_service = new PageService();
		$page_service->generate_growfund_pages();
    }
}
