<?php

namespace Growfund\DTO\Fund;

defined( 'ABSPATH' ) || exit;

use Growfund\DTO\Donation\DonationDTO;
use Growfund\DTO\DTO;
use Growfund\DTO\RevenueChartDTO;

class FundDetailsDTO extends DTO
{
    /**
     * @var string|int
     */
    public $id;

    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $description;

    /**
     * @var RevenueChartDTO[]
     */
    public $revenue;

    /**
     * @var DonationDTO[]
     */
    public $recent_donations;

    public $casts = [
        'revenue.*' => RevenueChartDTO::class,
        'recent_donations.*' => DonationDTO::class,
    ];
}
