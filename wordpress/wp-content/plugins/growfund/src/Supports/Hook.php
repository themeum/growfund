<?php

namespace Growfund\Supports;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\HookTypes;
use Growfund\Exceptions\InvalidHookException;
use Growfund\Hooks\BaseHook;

class Hook
{
    public static function register(array $hooks)
    {
        foreach ($hooks as $hook) {
            if (class_exists($hook) && is_subclass_of($hook, BaseHook::class)) {
                $instance = new $hook();

                if ($instance->get_type() === HookTypes::FILTER) {
                    add_filter($instance->get_name(), [$instance, 'handle'], $instance->get_priority(), $instance->get_args_count());
                } elseif ($instance->get_type() === HookTypes::ACTION) {
                    add_action($instance->get_name(), [$instance, 'handle'], $instance->get_priority(), $instance->get_args_count());
                }

            } else {
                throw new InvalidHookException(sprintf(
                    /* translators: %s: hook */
                    esc_html__("Hook class %s does not exist or is not a subclass of BaseHook.", 'growfund'), esc_html($hook)
                ));
            }
        }
    }
}
