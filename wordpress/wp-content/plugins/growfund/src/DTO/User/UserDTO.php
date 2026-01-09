<?php

namespace Growfund\DTO\User;

defined( 'ABSPATH' ) || exit;

use Growfund\CastAttributes\DateTimeAttribute;
use Growfund\CastAttributes\StringAttribute;
use Growfund\DTO\DTO;

class UserDTO extends DTO
{
    protected $casts = [
        'id' => StringAttribute::class,
        'joined_at' => DateTimeAttribute::class,
        'last_contribution_at' => DateTimeAttribute::class,
    ];

    /** @var string */
    public $id;

    /** @var string */
    public $first_name;

    /** @var string */
    public $last_name;

    /** @var string */
    public $display_name;

    /** @var string */
    public $email;

    /** @var string */
    public $username;

    /** @var object|null */
    public $image;

    /** @var string|null */
    public $phone;

    /** @var string */
    public $active_role;

    /** @var object|null */
    public $shipping_address;

    /** @var object|null */
    public $billing_address;

    /** @var bool */
    public $is_soft_deleted;

    /** @var bool */
    public $is_billing_address_same;

    /** @var object|null */
    public $notification_settings;

    /** @var \DateTime */
    public $joined_at;

    /** @var \DateTime|null */
    public $last_contribution_at;

    /** @var int */
    public $total_number_of_contributions;

    /** @var bool */
    public $is_verified;

    /** @var string */
    public $created_by;
}
