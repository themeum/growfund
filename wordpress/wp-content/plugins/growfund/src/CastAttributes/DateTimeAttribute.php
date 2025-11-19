<?php

namespace Growfund\CastAttributes;

defined( 'ABSPATH' ) || exit;

use Growfund\Contracts\CastAttribute;
use Growfund\Supports\Date;

class DateTimeAttribute implements CastAttribute
{
    public function get($value)
    {
        if (empty($value) || strtotime($value) === false) {
            return null;
        }

        return Date::zod_safe($value);
    }
}
