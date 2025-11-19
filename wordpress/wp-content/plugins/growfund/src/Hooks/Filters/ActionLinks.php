<?php

namespace Growfund\Hooks\Filters;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\HookTypes;
use Growfund\Hooks\BaseHook;
use InvalidArgumentException;

/**
 * Filter the action links to add a settings link
 *
 * @since 1.0.0
 */
class ActionLinks extends BaseHook
{
    public function get_name()
    {
        return 'plugin_action_links_' . plugin_basename(GROWFUND_PLUGIN_FILE);
    }

    public function get_type()
    {
        return HookTypes::FILTER;
    }

    public function handle(...$args)
    {
        if (empty($args)) {
            throw new InvalidArgumentException(esc_html__('Expected 1 argument, received 0', 'growfund'));
        }

        $actions = $args[0];

        $actions['settings'] = sprintf(
            '<a href="%s">%s</a>',
            admin_url('admin.php?page=growfund#/settings'),
            __('Settings', 'growfund')
        );

        return $actions;
    }
}
