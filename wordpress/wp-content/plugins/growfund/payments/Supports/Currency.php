<?php

namespace Growfund\Payments\Supports;

defined( 'ABSPATH' ) || exit;

class Currency
{
    const USD = 'USD';
    const EUR = 'EUR';

    public static function is_valid_iso(string $currency): bool
    {
        return preg_match('/^[A-Z]{3}$/', $currency);
    }
}
