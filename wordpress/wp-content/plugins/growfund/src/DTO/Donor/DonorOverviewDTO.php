<?php

namespace Growfund\DTO\Donor;

defined( 'ABSPATH' ) || exit;

use Growfund\CastAttributes\MoneyAttribute;
use Growfund\DTO\Activity\ActivityResponseDTO;
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
        'profile' => DonorDTO::class,
        'activity_logs.*' => ActivityResponseDTO::class,
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

    /** @var DonorDTO */
    public $profile;

    /** @var ActivityResponseDTO[] */
    public $activity_logs;
}
