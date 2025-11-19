<?php

namespace Growfund\DTO\Site\Auth;

defined( 'ABSPATH' ) || exit;

use Growfund\DTO\DTO;
use Growfund\Sanitizer;

class ResetPasswordDTO extends DTO
{
    /** @var string */
    public $password;

    /** @var string */
    public $password_confirmation;

    /** @var string */
    public $key;

    /** @var string */
    public $login;

    public static function sanitization_rules()
    {
        return [
            'password' => Sanitizer::TRIM,
            'password_confirmation' => Sanitizer::TRIM,
            'key' => Sanitizer::TRIM,
            'login' => Sanitizer::TRIM,
        ];
    }

    public static function validation_rules()
    {
        return [
            'password' => 'required|string|min:8',
            'password_confirmation' => 'required|string|same_as:password',
            'key' => 'required|string',
            'login' => 'required|string',
        ];
    }
}
