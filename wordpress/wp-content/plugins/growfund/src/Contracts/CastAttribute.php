<?php

namespace Growfund\Contracts;

defined( 'ABSPATH' ) || exit;

interface CastAttribute
{
    public function get($value);
}
