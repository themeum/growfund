<?php

namespace Growfund\Supports;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\Password;

/**
 * Class PasswordHelper
 *
 * Utility class for password generation and management.
 */
class PasswordHelper
{

    /**
     * Generate a random password
     *
     * @param int $length Password length (default: 12)
     * @param bool $special_chars Whether to include special characters (default: true)
     * @param bool $extra_special_chars Whether to include extra special characters (default: false)
     * @return string Generated password
     */
    public static function generate($length = Password::DEFAULT_LENGTH, $special_chars = Password::DEFAULT_SPECIAL_CHARS, $extra_special_chars = Password::DEFAULT_EXTRA_SPECIAL_CHARS)
    {
        return wp_generate_password($length, $special_chars, $extra_special_chars);
    }

    /**
     * Generate a simple password (no special characters)
     *
     * @param int $length Password length (default: 12)
     * @return string Generated password
     */
    public static function generate_simple($length = Password::DEFAULT_LENGTH)
    {
        return static::generate($length, false, false);
    }

    /**
     * Generate a password for new user registration
     *
     * @param int $length Password length (default: 12)
     * @return string Generated password
     */
    public static function generate_for_user($length = Password::DEFAULT_LENGTH)
    {
        return static::generate_simple($length);
    }
}
