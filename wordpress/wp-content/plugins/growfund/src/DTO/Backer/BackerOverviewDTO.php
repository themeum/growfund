<?php

namespace Growfund\DTO\Backer;

defined( 'ABSPATH' ) || exit;

use Growfund\CastAttributes\MoneyAttribute;
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
    ];

    /** @var float */
    public $pledged_amount;

    /** @var float */
    public $backed_amount;

    /** @var int */
    public $pledged_campaigns;

    /** @var int */
    public $backed_campaigns;

    /** @var \Growfund\DTO\Backer\BackerDTO */
    public $backer_information;

    /** @var \Growfund\DTO\Activity\ActivityResponseDTO[] */
    public $activity_logs;
}
