<?php

namespace Growfund\Hooks\UninstallActions;

defined( 'ABSPATH' ) || exit;

use Growfund\Contracts\Action;
use Growfund\Core\AppSettings;
use Growfund\Services\CleanupService;

class EraseApplicationData implements Action
{
    public function handle()
    {
        if (!growfund_settings(AppSettings::ADVANCED)->get('erase_data_upon_uninstallation', false)) {
            return;
        }

        (new CleanupService())->erase();
    }
}
