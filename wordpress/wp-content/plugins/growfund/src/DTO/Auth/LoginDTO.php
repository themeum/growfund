<?php

namespace Growfund\DTO\Auth;

defined( 'ABSPATH' ) || exit;

use Growfund\DTO\DTO;
use Growfund\Sanitizer;

class LoginDTO extends DTO
{
    /** @var string */
    public $user_login;

    /** @var string */
    public $password;

    public static function sanitization_rules()
    {
        return [
            'user_login' => Sanitizer::TRIM,
            'password' => Sanitizer::TRIM,
        ];
    }

    public static function validation_rules()
    {
        return [
            'user_login' => 'required|string',
            'password' => 'required|string|min:1',
        ];
    }
}
