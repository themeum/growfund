<?php

namespace Growfund\Contracts;

defined( 'ABSPATH' ) || exit;

/**
 * Interface Executor
 *
 * Represents a contract for executing logic.
 *
 * @since 1.1.0
 */
interface Executor
{
    /**
     * Execute the logic.
     *
     * @return void
     */
    public function run();
}
