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
class DonorDisplayDTO extends DTO
{
    protected $casts = [
        'max_contribution_amount' => MoneyAttribute::class,
        'donated_at' => DateTimeAttribute::class,
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

    /** @var string */
    public $donated_at;

    /** @var float */
    public $max_contribution_amount;

    /** @var float */
    public $total_contribution;
}
