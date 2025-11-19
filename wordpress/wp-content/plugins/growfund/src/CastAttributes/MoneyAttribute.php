<?php

namespace Growfund\CastAttributes;

defined( 'ABSPATH' ) || exit;

use Growfund\Contracts\CastAttribute;
use Growfund\Supports\Money;

class MoneyAttribute implements CastAttribute
{
    public function get($value)
    {
        if (is_null($value)) {
            return $value;
        }

        return Money::prepare_for_display($value);
    }
}
