<?php

namespace Growfund\DTO\Donor;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\UserTypes\Donor;
use Growfund\DTO\DTO;
use Growfund\Sanitizer;

class CreateDonorDTO extends DTO
{
    /**
     * Fields that are considered not part of "meta" data.
     *
     * @var array
     */
    protected static $base_fields = ['first_name', 'last_name', 'email', 'password', 'role'];

    /** @var string */
    public $first_name;

    /** @var string */
    public $last_name;

    /** @var string */
    public $email;

    /** @var string */
    public $username;

    /** @var string */
    public $password;

    /** @var string|null */
    public $phone;

    /** @var array|null */
    public $billing_address;

    /** @var int|null */
    public $image;

    /** @var string */
    public $status;

    /** @var string */
    public $role;

    /** @var int */
    public $created_by;

    public function __construct(array $data = [])
    {
        $this->role = Donor::ROLE;
        $this->status = Donor::DEFAULT_STATUS;
        parent::__construct($data);
    }

    public static function validation_rules()
    {
        return [
            'first_name'            => 'required|string',
            'last_name'             => 'required|string',
            'email'                 => 'required|email',
            'username'              => 'required|string',
            'password'              => 'required|string|min:6',
            'phone'                 => 'string',
            'image'                 => 'integer|is_valid_image_id',
            'billing_address'       => 'array',
            'billing_address.address' => 'string',
            'billing_address.address_2' => 'string',
            'billing_address.city'  => 'string',
            'billing_address.state' => 'string',
            'billing_address.zip_code' => 'string',
            'billing_address.country' => 'string',
        ];
    }

    public static function sanitization_rules()
    {
        return [
            'first_name'            => Sanitizer::TEXT,
            'last_name'             => Sanitizer::TEXT,
            'email'                 => Sanitizer::EMAIL,
            'username'              => Sanitizer::USERNAME,
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
