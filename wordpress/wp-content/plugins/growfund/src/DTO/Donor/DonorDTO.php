<?php

namespace Growfund\DTO\Donor;

defined( 'ABSPATH' ) || exit;

use Growfund\CastAttributes\DateTimeAttribute;
use Growfund\CastAttributes\MoneyAttribute;
use Growfund\DTO\DTO;

/**
 * Data Transfer Object for a Donor
 *
 * @since 1.0.0
 */
class DonorDTO extends DTO
{
    protected $casts = [
        'total_contributions' => MoneyAttribute::class,
        'joined_at' => DateTimeAttribute::class,
        'latest_donation_date' => DateTimeAttribute::class,
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
    public $phone;

    /** @var \Growfund\Supports\MediaAttachment */
    public $image;

    /** @var array */
    public $billing_address;

    /** @var string */
    public $joined_at;

    /** @var bool */
    public $is_verified;

    /** @var bool */
    public $is_fundraiser;

    /** @var string|null - optional */
    public $latest_donation_date;

    /** @var float|null - optional */
    public $total_contributions;

    /** @var int|null - optional */
    public $number_of_contributions;

    /** @var string*/
    public $created_by;
}
