<?php

namespace Growfund\DTO\Donation;

defined( 'ABSPATH' ) || exit;

use Growfund\CastAttributes\DateTimeAttribute;
use Growfund\DTO\DTO;

/**
 * Data Transfer Object for a Donor
 *
 * @since 1.0.0
 */
class DonationDonorDTO extends DTO
{
    protected $casts = [
        'joined_at' => DateTimeAttribute::class,
    ];

    /** @var int */
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

    /** @var \Growfund\Supports\MediaAttachment */
    public $image;

    /** @var array */
    public $billing_address;

    /** @var string */
    public $joined_at;

    /** @var bool */
    public $is_verified;

    /** @var string*/
    public $created_by;
}
