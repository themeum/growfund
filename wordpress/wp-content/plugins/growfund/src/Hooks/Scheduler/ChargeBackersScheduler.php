<?php

namespace Growfund\Hooks\Scheduler;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\HookNames;
use Growfund\Constants\HookTypes;
use Growfund\Hooks\BaseHook;
use Exception;

class ChargeBackersScheduler extends BaseHook
{
    public function get_name()
    {
        return HookNames::SCHEDULED_CHARGE_BACKERS;
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
        $service_class_name = $data['class'] ?? null;
        $service_args = $data['args'] ?? [];

        if (empty($service_class_name) || !class_exists($service_class_name)) {
            error_log( // phpcs:ignore
                sprintf(
                    /* translators: %s: service class */
                    __('The service class %s is not valid.', 'growfund'),
                    $service_class_name
                )
            );
            return;
        }

        $service = new $service_class_name($service_args);

        try {
            $service->charge();
        } catch (Exception $error) {
            error_log($error->getMessage()); // phpcs:ignore
        }
    }
}
