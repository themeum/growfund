<?php

namespace Growfund\DTO\Donation;

defined( 'ABSPATH' ) || exit;

use Growfund\DTO\DTO;

class AnnualReceiptFilterDTO extends DTO
{
    /** @var int */
    public $page = 1;

    /** @var int */
    public $limit = 10;
}
