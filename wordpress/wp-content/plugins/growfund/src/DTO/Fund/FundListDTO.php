<?php

namespace Growfund\DTO\Fund;

defined( 'ABSPATH' ) || exit;

use Growfund\CastAttributes\BooleanAttribute;
use Growfund\CastAttributes\DateTimeAttribute;
use Growfund\CastAttributes\MoneyAttribute;
use Growfund\DTO\DTO;

class FundListDTO extends DTO
{
    protected $casts = [
        'total_revenue' => MoneyAttribute::class,
        'is_default' => BooleanAttribute::class,
        'latest_donation_date' => DateTimeAttribute::class,
    ];

    /** @var string */
    public $id;

    /** @var string */
    public $title;

    /** @var string */
    public $description;

    /** @var bool */
    public $is_default;

    /** @var int */
    public $number_of_contributions;

    /** @var float */
    public $total_revenue;

    /** @var string */
    public $latest_donation_date;
}
