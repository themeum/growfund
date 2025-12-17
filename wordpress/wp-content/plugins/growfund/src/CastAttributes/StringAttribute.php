<?php

namespace Growfund\CastAttributes;

defined( 'ABSPATH' ) || exit;

use Growfund\Contracts\CastAttribute;

class StringAttribute implements CastAttribute
{
    public function get($value)
    {
        return (string) $value;
    }
}
