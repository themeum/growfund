<?php

namespace Growfund\DTO;

defined( 'ABSPATH' ) || exit;

use Growfund\CastAttributes\MoneyAttribute;

class RevenueChartDTO extends DTO
{
    protected $casts = [
        'revenue' => MoneyAttribute::class
    ];

    /** @var string */
    public $date;

    /** @var float */
    public $revenue;
}
