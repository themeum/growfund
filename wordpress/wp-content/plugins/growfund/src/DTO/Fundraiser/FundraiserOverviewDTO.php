<?php

namespace Growfund\DTO\Fundraiser;

defined( 'ABSPATH' ) || exit;

use Growfund\CastAttributes\MoneyAttribute;
use Growfund\DTO\Activity\ActivityResponseDTO;
use Growfund\DTO\DTO;

class FundraiserOverviewDTO extends DTO
{
    protected $casts = [
        'total_amount_received' => MoneyAttribute::class,
        'profile' => FundraiserDTO::class,
        'activity_logs.*' => ActivityResponseDTO::class
    ];

    /**
     * @var int
     */
    public $total_campaign_created;

    /**
     * @var int
     */
    public $total_amount_received;

    /**
     * @var int|null
     */
    public $total_successful_campaign;

    /**
     * @var int|null
     */
    public $total_failed_campaign;

    /** @var FundraiserDTO */
    public $profile;

    /** @var ActivityResponseDTO[] */
    public $activity_logs;
}
