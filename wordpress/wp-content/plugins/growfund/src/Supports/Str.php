<?php

namespace Growfund\Supports;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\AppConfigKeys;
use Growfund\Supports\Option;

/**
 * Class Money
 *
 * General utility for handling money-related operations such as preparing amounts for,
 * storage, display, etc.
 */
class Str
{
    /**
     * Convert a numeric string to a corresponding number.
     *
     * @param string $value
     * @return string|int|float
     */
    public static function to_number($value)
    {
        if (empty($value) || !is_numeric($value)) {
            return $value;
        }

        return strpos($value, '.') !== false || stripos($value, 'e') !== false
            ? (float) $value
            : (int) $value;
    }

    /**
     * Trim the value.
     *
     * @param string $value
     * @return string
     */
    public static function trim($value)
    {
        if (empty($value)) {
            return $value;
        }

        return trim($value);
    }

    /**
     * Decode a base64 string.
     *
     * @param string $value
     * @return string|false
     */
    public static function from_base64($value)
    {
        if (empty($value) || !preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $value)) {
            return false;
        }

        return base64_decode($value);
    }
}
