<?php

namespace Growfund\Core;

defined( 'ABSPATH' ) || exit;

use ActionScheduler_Store;
use Growfund\Constants\HookNames;
use Growfund\Constants\SchedulerTypes;
use Growfund\Contracts\RecurrableScheduler;
use Exception;
use InvalidArgumentException;

class Scheduler
{
    const DEFAULT_TIME_LIMIT = 60;
    const DEFAULT_BATCH_SIZE = 50;
    const DEFAULT_CONCURRENT_BATCHES = 8;

    protected static $instance = null;

    protected $resolve = null;
    protected $timestamp = 0;
    protected $interval = 0;
    protected $type = null;
    protected $data = [];
    protected $group = null;
    protected $unique = false;
    protected $priority = 10;

    protected function __construct()
    {
        add_filter(
            'action_scheduler_queue_runner_time_limit',
            function ($time_limit) {
                return min($time_limit * 3, static::DEFAULT_TIME_LIMIT);
            }
        );

        add_filter(
            'action_scheduler_queue_runner_concurrent_batches',
            function ($concurrent_batches) {
                return min($concurrent_batches * 2, static::DEFAULT_CONCURRENT_BATCHES);
            }
        );

        add_filter(
            'action_scheduler_queue_runner_batch_size',
            function ($batch_size) {
                return min($batch_size * 2, static::DEFAULT_BATCH_SIZE);
            }
        );

        add_filter(
            'action_scheduler_timeout_period',
            function ($timeout) {
                return $timeout * 3;
            }
        );

        add_filter(
            'action_scheduler_failure_period',
            function ($timeout) {
                return $timeout * 3;
            }
        );

        add_filter(
            'action_scheduler_default_cleaner_statuses',
            function ($statuses) {
                $statuses[] = ActionScheduler_Store::STATUS_FAILED;
                $statuses[] = ActionScheduler_Store::STATUS_COMPLETE;

                return $statuses;
            }
        );
    }

    /**
     * Make a singleton instance of the Scheduler class so that
     * The class can be used as a static method.
     *
     * @return self
     */
    public static function make()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * At when the scheduler will resolved.
     *
     * @param integer $timestamp
     * @return self
     */
    public function at(int $timestamp)
    {
        $this->timestamp = $timestamp;
        return $this;
    }

    /**
     * At what interval the scheduler will resolved.
     *
     * @param integer $interval
     * @return self
     */
    public function interval(int $interval)
    {
        if ($interval < MINUTE_IN_SECONDS) {
            /* translators: %s: MINUTE_IN_SECONDS */
            throw new InvalidArgumentException(sprintf(esc_html__('You have to set the interval more than %s.', 'growfund'), esc_html(MINUTE_IN_SECONDS)));
        }

        $this->interval = $interval;
        return $this;
    }

    /**
     * Define a scheduler type
     *
     * @param SchedulerTypes $type
     * @return self
     */
    public function type(string $type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get the hook name based on the scheduler type.
     *
     * @return string
     * @throws InvalidArgumentException
     */
    public function get_hook_name()
    {
        switch ($this->type) {
            case SchedulerTypes::EMAIL:
                return HookNames::SCHEDULED_EMAILS;
            case SchedulerTypes::CHARGE_BACKERS:
                return HookNames::SCHEDULED_CHARGE_BACKERS;
            case SchedulerTypes::RECURRING:
                return HookNames::SCHEDULED_RECURRING;
            default:
                throw new InvalidArgumentException(esc_html__('You have to set the ::type() to get the hook name of the scheduler.', 'growfund'));
        }
    }

    /**
     * The class will resolve after the scheduler is triggered.
     *
     * @param string $class_name
     * @return self
     */
    public function resolve(string $class_name)
    {
        $this->resolve = $class_name;

        return $this;
    }

    /**
     * Attach data to the scheduler.
     *
     * @param array $data
     * @return self
     */
    public function with(array $data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Define a group for the scheduler.
     *
     * @param string $group
     * @return self
     */
    public function group(string $group)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * Define if the scheduler is unique.
     *
     * @param boolean $unique
     * @return self
     */
    public function is_unique(bool $unique = true)
    {
        $this->unique = $unique;

        return $this;
    }

    /**
     * Define the priority of the scheduler.
     *
     * @param integer $priority
     * @return self
     */
    public function priority(int $priority)
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * Schedule the scheduler.
     *
     * @return int
     * @throws InvalidArgumentException
     */
    public function schedule()
    {
        $this->check();

        if (empty($this->timestamp)) {
            $this->timestamp = time();
        }

        if ($this->type === SchedulerTypes::RECURRING) {
            return $this->recurring_action();
        }

        return $this->single_action();
    }

    protected function single_action()
    {
        $args = [
            [
                'class' => $this->resolve,
                'args' => $this->data ?? [],
            ]
        ];
        $result = as_schedule_single_action(
            $this->timestamp,
            $this->get_hook_name(),
            $args,
            $this->group,
            $this->unique,
            $this->priority
        );

        $this->reset();

        return $result;
    }

    protected function recurring_action()
    {
        $args = [
            [
                'uid' => uniqid(),
                'class' => $this->resolve,
                'args' => $this->data ?? [],
                'recurring_group' => $this->group ?? ''
            ]
        ];
        $result = as_schedule_recurring_action(
            $this->timestamp,
            $this->interval,
            $this->get_hook_name(),
            $args,
            $this->group,
            $this->unique,
            $this->priority
        );

        $this->reset();

        return $result;
    }

    /**
     * Schedule the recurring scheduler.
     *
     * @return int action scheduler ID
     * @throws InvalidArgumentException
     */
    public function schedule_recurring()
    {
        $this->type(SchedulerTypes::RECURRING);
        return $this->schedule();
    }

    /**
     * Schedule email type actions.
     *
     * @return int
     * @throws InvalidArgumentException
     */
    public function schedule_email()
    {
        $this->type(SchedulerTypes::EMAIL);
        return $this->schedule();
    }

    /**
     * Schedule pledge charge type actions.
     *
     * @return int
     * @throws InvalidArgumentException
     */
    public function schedule_charge_backers()
    {
        $this->type(SchedulerTypes::CHARGE_BACKERS);
        return $this->schedule();
    }

    /**
     * Check the validity on before scheduling.
     *
     * @return void
     * @throws InvalidArgumentException
     */
    protected function check()
    {
        if (!function_exists('as_schedule_single_action')) {
            throw new Exception(esc_html__('The Action Scheduler is not configured properly.', 'growfund'));
        }

        if (empty($this->type)) {
            throw new InvalidArgumentException(esc_html__('You have to set the ::type() to define the type of the scheduler.', 'growfund'));
        }

        if (empty($this->resolve) || !class_exists($this->resolve)) {
            throw new InvalidArgumentException(esc_html__('You have to set ::resolve() to define the class that will resolve after the scheduler is triggered.', 'growfund'));
        }

        if ($this->type === SchedulerTypes::RECURRING) {
            if (empty($this->interval)) {
                throw new InvalidArgumentException(esc_html__('You have to set the ::interval() to define the interval of the recurring scheduler.', 'growfund'));
            }

            if (!is_subclass_of($this->resolve, RecurrableScheduler::class)) {
                /* translators: 1: resolving class, 2: RecurrableScheduler::class */
                throw new InvalidArgumentException(sprintf(esc_html__('The class %1$s is not valid. it should be a subclass of %2$s', 'growfund'), esc_html($this->resolve), RecurrableScheduler::class));
            }
        }
    }

    protected function reset()
    {
        $this->timestamp = 0;
        $this->type = null;
        $this->data = [];
        $this->group = null;
        $this->unique = false;
        $this->priority = 10;
        $this->resolve = null;
    }
}
