<?php

namespace Growfund\Hooks\Scheduler;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\HookNames;
use Growfund\Constants\HookTypes;
use Growfund\Hooks\BaseHook;
use Growfund\Mailer;
use Exception;

class EmailScheduler extends BaseHook
{
    public function get_name()
    {
        return HookNames::SCHEDULED_EMAILS;
    }

    public function get_type()
    {
        return HookTypes::ACTION;
    }

    public function handle(...$args)
    {
        if (empty($args)) {
            return;
        }

        $data = $args[0];
        $mailer_class_name = $data['class'] ?? null;
        $mailer_args = $data['args'] ?? [];

        if (empty($mailer_class_name) || !class_exists($mailer_class_name) || !is_subclass_of($mailer_class_name, Mailer::class)) {
            growfund_error_log(
                sprintf(
                    /* translators: %s: mailer class */
                    __('The mailer class %s is not valid.', 'growfund'),
                    $mailer_class_name
                )
            );
            return;
        }

        try {
            growfund_email($mailer_class_name)
                ->with($mailer_args)
                ->send();
        } catch (Exception $error) {
            growfund_error_log($error->getMessage());
        }
    }
}
