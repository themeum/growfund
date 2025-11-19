<?php

namespace Growfund\DTO\User;

defined( 'ABSPATH' ) || exit;

use Growfund\DTO\DTO;
use Growfund\Sanitizer;

class UpdatePasswordDTO extends DTO
{
    /** @var string|null */
    public $id;

    /** @var string */
    public $current_password;

    /** @var string */
    public $new_password;

    /** @var string */
    public $confirm_password;

    public static function validation_rules()
    {
        return [
            'id' => 'required|integer',
            'current_password' => 'required|string|min:6',
            'new_password' => 'required|string|min:6|same_as:confirm_password',
        ];
    }

    public static function sanitization_rules()
    {
        return [
            'id' => Sanitizer::INT,
            'current_password' => Sanitizer::TEXT,
            'new_password' => Sanitizer::TEXT,
            'confirm_password' => Sanitizer::TEXT,
        ];
    }
}
