<?php

namespace Growfund\DTO;

defined( 'ABSPATH' ) || exit;

use Growfund\CastAttributes\MoneyAttribute;

class RevenueBreakdownDTO extends DTO
{
    protected $casts = [
        'total_contributions' => MoneyAttribute::class,
        'pledged_amount' => MoneyAttribute::class,
        'net_payout' => MoneyAttribute::class,
    ];

    /** @var string */
    public $date;

    /** @var int */
    public $number_of_contributors;

    /** @var float */
    public $total_contributions;

    /** @var float */
    public $pledged_amount;

    /** @var float */
    public $net_payout;
}
