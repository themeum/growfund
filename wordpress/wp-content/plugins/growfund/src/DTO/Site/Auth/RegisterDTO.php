<?php

namespace Growfund\DTO\Site\Auth;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\UserTypes\Backer;
use Growfund\Constants\UserTypes\Donor;
use Growfund\Constants\UserTypes\Fundraiser;
use Growfund\DTO\DTO;
use Growfund\Sanitizer;

class RegisterDTO extends DTO
{
    /** @var string */
    public $first_name;

    /** @var string */
    public $last_name;

    /** @var string */
    public $email;

    /** @var string */
    public $password;

    /** @var string */
    public $password_confirmation;

    /** @var string */
    public $role;

    /** @var string|null */
    public $redirect_to;

    public static function validation_rules()
    {
        return [
            'first_name' => 'required|string|min:2',
            'last_name' => 'required|string|min:2',
            'email' => 'required|email|email_unique',
            'password' => 'required|string|min:8',
            'password_confirmation' => 'required|string|same_as:password',
            'role' => 'string|in:' . Donor::ROLE . ',' . Backer::ROLE . ',' . Fundraiser::ROLE,
            'redirect_to' => 'string'
        ];
    }

    public static function sanitization_rules()
    {
        return [
            'first_name' => Sanitizer::TEXT,
            'last_name' => Sanitizer::TEXT,
            'email' => Sanitizer::EMAIL,
            'password' => Sanitizer::TRIM,
            'password_confirmation' => Sanitizer::TRIM,
            'role' => Sanitizer::TEXT,
            'redirect_to' => Sanitizer::URL,
        ];
    }
}
