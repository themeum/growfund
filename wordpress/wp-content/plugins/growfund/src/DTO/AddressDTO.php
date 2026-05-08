<?php

namespace Growfund\DTO;

use Growfund\CastAttributes\StringAttribute;

defined( 'ABSPATH' ) || exit;

class AddressDTO extends DTO
{
    /** @var string */
    public $address;

    /** @var string|null */
    public $address_2;

    /** @var string */
    public $city;

    /** @var string */
    public $zip_code;

    /** @var string */
    public $country;

    /** @var string|null */
    public $state;

    public $casts = [
        'address' => StringAttribute::class,
        'city' => StringAttribute::class,
        'zip_code' => StringAttribute::class,
        'country' => StringAttribute::class,
    ];
}
