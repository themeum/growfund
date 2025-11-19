<?php

namespace Growfund\DTO\Donation;

defined( 'ABSPATH' ) || exit;

use Growfund\DTO\DTO;

class DonationFundDTO extends DTO
{
    /** @var string */
    public $id;

    /** @var string */
    public $title;
}
