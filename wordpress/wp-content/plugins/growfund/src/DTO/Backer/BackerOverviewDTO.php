<?php

namespace Growfund\DTO\Backer;

defined( 'ABSPATH' ) || exit;

use Growfund\CastAttributes\MoneyAttribute;
use Growfund\DTO\Activity\ActivityResponseDTO;
use Growfund\DTO\DTO;

class BackerOverviewDTO extends DTO
{
    /**
     * Fields that are considered not part of "meta" data.
     *
     * @var array
     */
    protected static $base_fields = [];

    protected $casts = [
        'pledged_amount'    => MoneyAttribute::class,
        'backed_amount' => MoneyAttribute::class,
        'backer_information' => BackerDTO::class,
        'activity_logs.*' => ActivityResponseDTO::class
    ];

    /** @var float */
    public $pledged_amount;

    /** @var float */
    public $backed_amount;

    /** @var int */
    public $pledged_campaigns;

    /** @var int */
    public $backed_campaigns;

    /** @var BackerDTO */
    public $backer_information;

    /** @var ActivityResponseDTO[] */
    public $activity_logs;
}
