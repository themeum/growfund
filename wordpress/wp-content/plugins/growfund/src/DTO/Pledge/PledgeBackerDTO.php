<?php

namespace Growfund\DTO\Pledge;

defined( 'ABSPATH' ) || exit;

use Growfund\CastAttributes\ArrayAttribute;
use Growfund\CastAttributes\DateTimeAttribute;
use Growfund\DTO\DTO;

/**
 * Data Transfer Object for PledgeCampaign
 *
 * @since 1.0.0
 */
class PledgeBackerDTO extends DTO
{
    /**
     * Fields that are considered not part of "meta" data.
     *
     * @var array
     */
    protected static $base_fields = [];

    protected $casts = [
        'shipping_address' => ArrayAttribute::class,
        'billing_address' => ArrayAttribute::class,
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
    public $phone;

    /** @var \Growfund\Supports\MediaAttachment */
    public $image;

    /** @var array */
    public $shipping_address;

    /** @var array */
    public $billing_address;

    /** @var bool */
    public $is_billing_address_same;

    /** @var string */
    public $joined_at;

    /** @var bool */
    public $is_verified;
}
