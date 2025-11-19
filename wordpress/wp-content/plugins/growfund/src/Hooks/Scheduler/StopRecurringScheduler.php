<?php

namespace Growfund\Hooks\Scheduler;

defined( 'ABSPATH' ) || exit;

use ActionScheduler;
use ActionScheduler_Store;
use Growfund\Constants\HookNames;
use Growfund\Constants\HookTypes;
use Growfund\Hooks\BaseHook;

class StopRecurringScheduler extends BaseHook
{
    public function get_name()
    {
        return HookNames::STOP_SCHEDULED_RECURRING;
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

        if (empty($data['action_id'])) {
            return;
        }

        $store = ActionScheduler::store();
        $action = $store->fetch_action($data['action_id']);
        $action_args = $action->get_args() ?? [];
        $uid = $action_args[0]['uid'] ?? '';
        $group = $action->get_group();
        $hook = $action->get_hook();

        $scheduled_action = $this->get_scheduled_action($hook, $uid, $group);


        if (empty($scheduled_action)) {
            error_log( // phpcs:ignore
                sprintf(
                    /* translators: %s: action ID */
                    __('Failed to stop recurring action with ID: %s', 'growfund'),
                    $data['action_id']
                )
            );

            return;
        }

        $store->delete_action($scheduled_action['action_id']);
    }

    protected function get_scheduled_action(string $hook, string $uid, string $group)
    {
        if (empty($uid)) {
            return null;
        }

        $store = ActionScheduler::store();

        $action_ids = $store->query_actions([
            'per_page' => -1,
            'hook'     => $hook,
            'group'    => $group,
            'status'   => ActionScheduler_Store::STATUS_PENDING,
            'search'   => $uid
        ]);

        foreach ($action_ids as $action_id) {
            $action = $store->fetch_action($action_id);
            $action_args = $action->get_args() ?? [];

            if (!empty($action_args[0]['uid']) && $action_args[0]['uid'] === $uid) {
                return [
                    'action_id' => $action_id,
                    'group' => $group,
                    'action_args' => $action_args,
                ];
            }
        }

        return null;
    }
}
