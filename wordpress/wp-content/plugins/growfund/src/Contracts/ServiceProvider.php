<?php

namespace Growfund\Contracts;

defined( 'ABSPATH' ) || exit;

interface ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @param array $args
     * @return void
     */
    public function register(...$args);
}
