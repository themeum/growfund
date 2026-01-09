<?php

namespace Growfund\DTO\Site\Auth;

defined( 'ABSPATH' ) || exit;

use Growfund\DTO\DTO;
use Growfund\Sanitizer;

class LoginDTO extends DTO
{
    /** @var string */
    public $user_login;

    /** @var string */
    public $password;

    /** @var string|null */
    public $redirect_to;

    public static function sanitization_rules()
    {
        return [
            'user_login' => Sanitizer::TRIM,
            'password' => Sanitizer::TRIM,
            'redirect_to' => Sanitizer::TRIM,
        ];
    }

    public static function validation_rules()
    {
        return [
            'user_login' => 'required|string',
            'password' => 'required|string|min:1',
            'redirect_to' => 'string|nullable'
        ];
    }
}
