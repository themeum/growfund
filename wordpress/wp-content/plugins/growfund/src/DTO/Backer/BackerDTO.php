<?php

namespace Growfund\DTO\Backer;

defined( 'ABSPATH' ) || exit;

use Growfund\CastAttributes\BooleanAttribute;
use Growfund\CastAttributes\DateTimeAttribute;
use Growfund\CastAttributes\MoneyAttribute;
use Growfund\DTO\AddressDTO;
use Growfund\DTO\DTO;

class BackerDTO extends DTO
{
    /**
     * Fields that are considered not part of "meta" data.
     *
     * @var array
     */
    protected static $base_fields = [];

    protected $casts = [
        'is_billing_address_same' => BooleanAttribute::class,
        'shipping_address' => AddressDTO::class,
        'billing_address' => AddressDTO::class,
        'total_contributions' => MoneyAttribute::class,
        'latest_pledge_date' => DateTimeAttribute::class,
        'joined_at' => DateTimeAttribute::class,
    ];

    /** @var string */
    public $id;

    /** @var string */
    public $first_name;

    /** @var string */
    public $last_name;

    /** @var string */
    public $email;

    /** @var string */
    public $username;

    /** @var string */
    public $phone;

    /** @var \Growfund\Supports\MediaAttachment|null */
    public $image;

    /** @var int */
    public $number_of_contributions;

    /** @var float */
    public $total_contributions;

    /** @var string */
    public $latest_pledge_date;

    /** @var string */
    public $joined_at;

    /** @var AddressDTO|null */
    public $shipping_address;

    /** @var AddressDTO|null */
    public $billing_address;

    /** @var bool */
    public $is_billing_address_same;

    /** @var bool */
    public $is_verified;

    /** @var bool */
    public $is_fundraiser;

    /** @var string */
    public $created_by;
}
