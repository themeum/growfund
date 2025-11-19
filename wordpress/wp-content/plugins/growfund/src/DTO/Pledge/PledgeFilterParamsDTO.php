<?php

namespace Growfund\DTO\Pledge;

defined( 'ABSPATH' ) || exit;

use Growfund\DTO\DTO;

class PledgeFilterParamsDTO extends DTO
{
    /** @var int|null */
    public $user_id;

    /** @var int|null */
    public $campaign_id;

    /** @var int|null */
    public $fundraiser_id;

    /** @var string|array|null */
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
}
