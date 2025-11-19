<?php

namespace Growfund\DTO\Backer;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\UserTypes\Backer;
use Growfund\DTO\DTO;
use Growfund\Sanitizer;

class CreateBackerDTO extends DTO
{
    /**
     * Fields that are considered not part of "meta" data.
     *
     * @var array
     */
    protected static $base_fields = ['first_name', 'last_name', 'email', 'password', 'role'];

    public $first_name;

    public $last_name;

    public $email;

    public $password;

    public $phone;

    public $image;

    public $status;

    public $role;

    public $shipping_address;

    public $billing_address;

    public $is_billing_address_same;

    /** @var int */
    public $created_by;


    public function __construct(array $data = [])
    {
        $this->role = Backer::ROLE;
        $this->status = Backer::DEFAULT_STATUS;
        parent::__construct($data);
    }

    public static function validation_rules()
    {
        return [
            'first_name'                    => 'required|string',
            'last_name'                     => 'required|string',
            'email'                         => 'required|email',
            'password'                      => 'required|string|min:6',
            'phone'                         => 'string',
            'shipping_address'              => 'required|array',
            'shipping_address.address'      => 'required|string',
            'shipping_address.address_2'    => 'string',
            'shipping_address.city'         => 'required|string',
            'shipping_address.state'        => 'string',
            'shipping_address.zip_code'     => 'required|string',
            'shipping_address.country'      => 'required|string',
            'billing_address'               => 'required_if:is_billing_address_same,false|array',
            'billing_address.address'       => 'required_if:is_billing_address_same,false|string',
            'billing_address.address_2'     => 'required_if:is_billing_address_same,false|string',
            'billing_address.city'          => 'required_if:is_billing_address_same,false|string',
            'billing_address.state'         => 'required_if:is_billing_address_same,false|string',
            'billing_address.zip_code'      => 'required_if:is_billing_address_same,false|string',
            'billing_address.country'       => 'required_if:is_billing_address_same,false|string',
            'is_billing_address_same'       => 'boolean',
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
            'shipping_address'              => Sanitizer::ARRAY,
            'shipping_address.address'      => Sanitizer::TEXT,
            'shipping_address.address_2'    => Sanitizer::TEXT,
            'shipping_address.city'         => Sanitizer::TEXT,
            'shipping_address.state'        => Sanitizer::TEXT,
            'shipping_address.zip_code'     => Sanitizer::TEXT,
            'shipping_address.country'      => Sanitizer::TEXT,
            'billing_address'               => Sanitizer::ARRAY,
            'billing_address.address'       => Sanitizer::TEXT,
            'billing_address.address_2'     => Sanitizer::TEXT,
            'billing_address.city'          => Sanitizer::TEXT,
            'billing_address.state'         => Sanitizer::TEXT,
            'billing_address.zip_code'      => Sanitizer::TEXT,
            'billing_address.country'       => Sanitizer::TEXT,
            'is_billing_address_same'       => Sanitizer::BOOL,
            'image'                         => Sanitizer::INT
        ];
    }
}
