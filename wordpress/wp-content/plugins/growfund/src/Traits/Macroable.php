<?php

namespace Growfund\Traits;

defined( 'ABSPATH' ) || exit;

use BadMethodCallException;

trait Macroable
{
    /** @var array */
    protected static $macros = [];

    /**
     * Register a macro.
     *
     * @param string $name
     * @param callable $macro
     * @return void
     */
    public static function macro($name, callable $macro)
    {
        static::$macros[$name] = $macro;
    }

    /**
     * Dynamically handle calls to the class.
     *
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public function __call(string $method, array $arguments)
    {
        if (isset(static::$macros[$method])) {
            return call_user_func_array(
                static::$macros[$method]->bindTo($this, static::class),
                $arguments
            );
        }

        throw new BadMethodCallException(
            sprintf('Method %s::%s does not exist.', static::class, esc_html($method))
        );
    }

    /**
     * Dynamically handle calls to the class.
     *
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public static function __callStatic(string $method, array $arguments)
    {
        if (isset(static::$macros[$method])) {
            return call_user_func_array(
                static::$macros[$method]->bindTo(null, static::class),
                $arguments
            );
        }

        throw new BadMethodCallException(
            sprintf('Method %s::%s does not exist.', static::class, esc_html($method))
        );
    }
}
