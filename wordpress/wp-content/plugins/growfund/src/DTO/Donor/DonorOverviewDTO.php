<?php

namespace Growfund\DTO\Donor;

defined( 'ABSPATH' ) || exit;

use Growfund\CastAttributes\MoneyAttribute;
use Growfund\DTO\DTO;

/**
 * Data Transfer Object for a Donor
 *
 * @since 1.0.0
 */
class DonorOverviewDTO extends DTO
{
    protected $casts = [
        'total_contributions' => MoneyAttribute::class,
        'average_donation' => MoneyAttribute::class,
    ];

    /** @var int */
    public $id;

    /** @var float */
    public $total_contributions;

    /** @var float */
    public $average_donation;

    /** @var int */
    public $donated_campaigns;

    /** @var int */
    public $number_of_contributions;

    /** @var \Growfund\DTO\Donor\DonorDTO */
    public $profile;

    /** @var \Growfund\DTO\Activity\ActivityResponseDTO[] */
    public $activity_logs;
}
