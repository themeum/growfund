<?php

namespace Growfund\Hooks\Scheduler;

defined( 'ABSPATH' ) || exit;

use ActionScheduler;
use ActionScheduler_IntervalSchedule;
use Growfund\Constants\HookNames;
use Growfund\Constants\HookTypes;
use Growfund\Constants\WP;
use Growfund\Contracts\RecurrableScheduler;
use Growfund\Hooks\BaseHook;
use Growfund\QueryBuilder;
use Exception;

/**
 * @since 1.0.0
 * 
 * available action hooks in wp action schedulers : action_scheduler_after_execute, action_scheduler_stored_action, action_scheduler_completed_action
 */
class RecurringScheduler extends BaseHook
{
    public function get_name()
    {
        return HookNames::SCHEDULED_RECURRING;
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
        $class_name = $data['class'] ?? null;
        $args = $data['args'] ?? [];
        $group = $data['recurring_group'] ?? '';
        $uid = $data['uid'] ?? '';


        if (empty($class_name) || !class_exists($class_name) || !is_subclass_of($class_name, RecurrableScheduler::class)) {
            error_log( // phpcs:ignore
                sprintf(
                    /* translators: 1: resolving class, 2: RecurrableScheduler::class */
                    __('The class %1$s is not valid. it should be a subclass of %2$s', 'growfund'),
                    $class_name,
                    RecurrableScheduler::class
                )
            );

            return;
        }

        $class_instance = new $class_name($args);

        if ($class_instance->should_stop()) {
            $this->remove_scheduler($uid, $group);

            return;
        }

        try {
            $class_instance->handle();

            if (!empty($class_instance->get_additional_args())) {
                $new_args = [
                    [
                        'uid' => $uid,
                        'class' => $class_name,
                        'args' =>  array_merge($args, $class_instance->get_additional_args()),
                        'recurring_group' => $group
                    ]
                ];
                $this->update_with_new_args($class_name, $new_args, $uid, $group);
            }
        } catch (Exception $error) {
            error_log($error->getMessage()); // phpcs:ignore
        }
    }

    /**
     * Delete a recurring action.
     *
     */
    protected function remove_scheduler()
    {
        add_action('action_scheduler_completed_action', function ($action_id) {
            $store = ActionScheduler::store();
            $action = $store->fetch_action($action_id);
            $schedule = $action->get_schedule();
            $next_date_unix = time();

            if (!empty($schedule) && $schedule instanceof ActionScheduler_IntervalSchedule) {
                $next_date_unix = $schedule->get_date()->getTimestamp();
            }

            as_schedule_single_action(
                $next_date_unix - MINUTE_IN_SECONDS + 15,
                HookNames::STOP_SCHEDULED_RECURRING,
                [['action_id' => $action_id]],
                'growfund_stop_recurring_scheduler',
                false,
                1
            );
        });
    }


    /**
     * Reschedule a recurring action with new arguments.
     * 
     * @param string $class_name The class name of the action to reschedule.
     * @param array $new_args The new arguments to pass to the action.
     */
    protected function update_with_new_args(string $class_name, array $new_args, string $uid, string $group)
    {
        add_action('action_scheduler_stored_action', function ($action_id) use ($class_name, $new_args, $uid, $group) {
            $store = ActionScheduler::store();
            $action = $store->fetch_action($action_id);
            $action_group = $action->get_group();
            $action_uid = $action->get_args()[0]['uid'] ?? '';
            $action_class = $action->get_args()[0]['class'] ?? '';

            if ($action_uid === $uid && $action_class === $class_name && $action_group === $group) {
                $extended_args = wp_json_encode($new_args, JSON_UNESCAPED_SLASHES);
                $is_updated = QueryBuilder::query()->table(WP::ACTION_SCHEDULER_ACTIONS_TABLE)
                    ->where('action_id', $action_id)
                    ->update([
                        'extended_args' => $extended_args
                    ]);

                if (empty($is_updated)) {
                    error_log( // phpcs:ignore
                        sprintf(
                            /* translators: %s: action ID */
                            __('Failed to update recurring action with ID: %s', 'growfund'),
                            $action_id
                        )
                    );
                }
            }
        }, 1);
    }
}
