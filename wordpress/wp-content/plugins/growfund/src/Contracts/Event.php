<?php

namespace Growfund\Contracts;

defined( 'ABSPATH' ) || exit;

interface Event
{
    public function handle();
}
