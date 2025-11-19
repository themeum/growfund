<?php

namespace Growfund\DTO\Fundraiser;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\UserTypes\Fundraiser;
use Growfund\DTO\DTO;
use Growfund\Sanitizer;

class UpdateFundraiserDTO extends DTO
{
    /**
     * Fields that are considered not part of "meta" data.
     *
     * @var array
     */
    protected static $base_fields = ['first_name', 'last_name', 'email', 'password'];

    public $first_name;

    public $last_name;

    public $email;

    public $password;

    public $phone;

    public $image;

    public static function validation_rules()
    {
        return [
            'first_name'                    => 'required|string',
            'last_name'                     => 'required|string',
            'email'                         => 'required|email',
            'password'                      => 'string|min:6',
            'phone'                         => 'string',
            'image'                         => 'integer|is_valid_image_id',
        ];
    }

    public static function sanitization_rules()
    {
        return [
            'first_name'                    => Sanitizer::TEXT,
            'last_name'                     => Sanitizer::TEXT,
            'email'                         => Sanitizer::EMAIL,
            'password'                      => Sanitizer::TEXT,
            'phone'                         => Sanitizer::TEXT,
            'image'                         => Sanitizer::INT,
        ];
    }
}
