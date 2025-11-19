<?php

namespace Growfund\Contracts;

defined( 'ABSPATH' ) || exit;

interface Constant
{
    /**
     * Get all the constants
     * 
     * @return array
     */
    public static function all();
}
