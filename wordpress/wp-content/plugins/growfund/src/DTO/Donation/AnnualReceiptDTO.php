<?php

namespace Growfund\DTO\Donation;

defined( 'ABSPATH' ) || exit;

use Growfund\DTO\DTO;
use Growfund\DTO\User\UserDTO;

/**
 * Data Transfer Object for a Donation record
 *
 * @since 1.0.0
 */
class AnnualReceiptDTO extends DTO
{
    /** @var AnnualReceiptDonationDTO[] */
    public $donations;

    /** @var UserDTO|null */
    public $donor;

    public $casts = [
        'donations.*' => AnnualReceiptDonationDTO::class,
        'donor' => UserDTO::class
    ];
}
