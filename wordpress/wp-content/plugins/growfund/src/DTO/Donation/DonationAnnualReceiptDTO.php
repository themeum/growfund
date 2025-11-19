<?php

namespace Growfund\DTO\Donation;

defined( 'ABSPATH' ) || exit;

use Growfund\CastAttributes\MoneyAttribute;
use Growfund\DTO\DTO;

class DonationAnnualReceiptDTO extends DTO
{
    protected $casts = [
        'total_donations' => MoneyAttribute::class,
    ];

    /** @var string */
    public $id;

    /** @var string */
    public $uid;

    /** @var string */
    public $year;

    /** @var float */
    public $total_donations;

    /** @var int */
    public $number_of_donations;
}
