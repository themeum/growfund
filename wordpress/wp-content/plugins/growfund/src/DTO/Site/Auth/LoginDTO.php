<?php

namespace Growfund\DTO\Site\Auth;

defined( 'ABSPATH' ) || exit;

use Growfund\DTO\DTO;
use Growfund\Sanitizer;

class LoginDTO extends DTO
{
    /** @var string */
    public $email;

    /** @var string */
    public $password;

    /** @var string|null */
    public $redirect_to;

    public static function sanitization_rules()
    {
        return [
            'email' => Sanitizer::EMAIL,
            'password' => Sanitizer::TRIM,
            'redirect_to' => Sanitizer::TRIM,
        ];
    }

    public static function validation_rules()
    {
        return [
            'email' => 'required|email',
            'password' => 'required|string|min:1',
            'redirect_to' => 'string|nullable'
        ];
    }
}
