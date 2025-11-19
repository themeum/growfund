<?php

namespace Growfund\CastAttributes;

defined( 'ABSPATH' ) || exit;

use Growfund\Contracts\CastAttribute;

class BooleanAttribute implements CastAttribute
{
    public function get($value)
    {
        if (is_bool($value)) {
            return $value;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN) ?? false;
    }
}
