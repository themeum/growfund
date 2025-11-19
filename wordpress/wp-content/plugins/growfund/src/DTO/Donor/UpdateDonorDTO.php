<?php

namespace Growfund\DTO\Donor;

defined( 'ABSPATH' ) || exit;

use Growfund\DTO\DTO;
use Growfund\Sanitizer;

class UpdateDonorDTO extends DTO
{
    /**
     * Fields that are considered not part of "meta" data.
     *
     * @var array
     */
    protected static $base_fields = ['first_name', 'last_name', 'email', 'password'];

    /** @var string */
    public $first_name;

    /** @var string */
    public $last_name;

    /** @var string */
    public $email;

    /** @var string|null */
    public $password;

    /** @var string|null */
    public $phone;

    /** @var array|null */
    public $billing_address;

    /** @var int|null */
    public $image;

    public static function validation_rules()
    {
        return [
            'first_name'            => 'required|string',
            'last_name'             => 'required|string',
            'email'                 => 'required|email',
            'password'              => 'string|min:6',
            'phone'                 => 'string',
            'image'                 => 'integer|is_valid_image_id',
            'billing_address'               => 'array',
            'billing_address.address'       => 'string',
            'billing_address.address_2'     => 'string',
            'billing_address.city'          => 'string',
            'billing_address.state'         => 'string',
            'billing_address.zip_code'      => 'string',
            'billing_address.country'       => 'string',
        ];
    }

    public static function sanitization_rules()
    {
        return [
            'first_name'            => Sanitizer::TEXT,
            'last_name'             => Sanitizer::TEXT,
            'email'                 => Sanitizer::EMAIL,
            'password'              => Sanitizer::TEXT,
            'phone'                 => Sanitizer::TEXT,
            'image'                 => Sanitizer::INT,
            'billing_address'               => Sanitizer::ARRAY,
            'billing_address.address'       => Sanitizer::TEXT,
            'billing_address.address_2'     => Sanitizer::TEXT,
            'billing_address.city'          => Sanitizer::TEXT,
            'billing_address.state'         => Sanitizer::TEXT,
            'billing_address.zip_code'      => Sanitizer::TEXT,
            'billing_address.country'       => Sanitizer::TEXT,
        ];
    }
}
