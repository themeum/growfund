<?php

namespace Growfund\DTO\Fund;

defined( 'ABSPATH' ) || exit;

use Growfund\DTO\DTO;

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
     * @var \Growfund\DTO\RevenueChartDTO[]
     */
    public $revenue;

    /**
     * @var \Growfund\DTO\Donation\DonationDTO[]
     */
    public $recent_donations;
}
