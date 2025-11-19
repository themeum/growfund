<?php

namespace Growfund\DTO\Donation;

defined( 'ABSPATH' ) || exit;

use Growfund\DTO\DTO;

class DonationFilterParamsDTO extends DTO
{
    /** @var int|null */
    public $user_id;

    /** @var int|null */
    public $fundraiser_id;

    /** @var int|null */
    public $campaign_id;

    /** @var int|null */
    public $fund_id;

    /** @var string|null */
    public $status;

    /** @var string|null (format: Y-m-d or Y-m-d H:i:s) */
    public $start_date;

    /** @var string|null (format: Y-m-d or Y-m-d H:i:s) */
    public $end_date;

    /** @var string|null */
    public $search;

    /** @var int */
    public $page = 1;

    /** @var int */
    public $limit = 10;

    /** @var string|null */
    public $orderby;

    /** @var string|null */
    public $order;
}
