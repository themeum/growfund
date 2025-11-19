<?php

namespace Growfund\Core;

defined( 'ABSPATH' ) || exit;

use Exception;
use ReflectionClass;

class Dispatcher
{
    protected $listeners = [];

    public function __construct()
    {
        $this->listeners = require GROWFUND_DIR_PATH . 'bootstrap/events.php';
    }

    /**
     * Register a listener for an event.
     *
     * @param string $event The event to listen for.
     * @param string $listener The listener to register.
     * @return void
     */
    public function listen(string $event, string $listener)
    {
        $this->listeners[$event][] = $listener;
    }

    /**
     * Dispatch an event to its listeners.
     *
     * @param object|string $event The event to dispatch.
     * @return void
     */
    public function dispatch($event)
    {
        foreach ($this->get_listeners($event) as $listener) {
            $this->make_listener($listener)->handle(
                $this->make_event($event)
            );
        }
    }

    /**
     * Get the listeners for an event.
     *
     * @param object $event The event to get listeners for.
     * @return array The listeners for the event.
     */
    protected function get_listeners($event)
    {
        $event = is_object($event) ? get_class($event) : $event;

        return !empty($this->listeners[$event]) ? $this->listeners[$event] : [];
    }

    /**
     * Make a listener instance.
     *
     * @param string $listener The listener to make.
     * @return object The listener instance.
     */
    protected function make_listener($listener)
    {
        if (!class_exists($listener)) {
            throw new Exception(sprintf(
                /* translators: %s: Listener name */
                esc_html__('Listener %s not found', 'growfund'),
                esc_html($listener)
            ));
        }

        return new $listener();
    }

    /**
     * Make an event instance.
     *
     * @param object|string $event The event to make.
     * @return object The event instance.
     */
    protected function make_event($event)
    {
        if (is_object($event)) {
            return $event;
        }

        $reflector = new ReflectionClass($event);
        $constructor = $reflector->getConstructor();

        if ($constructor && $constructor->getNumberOfParameters() > 0) {
            return $reflector->newInstanceWithoutConstructor();
        }

        return new $event();
    }
}
