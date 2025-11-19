<?php

namespace Growfund\DTO\Activity;

defined( 'ABSPATH' ) || exit;

use Growfund\DTO\DTO;

class ActivityFilterDTO extends DTO
{
    /** @var int */
    public $campaign_id;

    /** @var int */
    public $pledge_id;

    /** @var int */
    public $donation_id;

    /** @var int */
    public $user_id;

    /** @var int */
    public $page;

    /** @var int */
    public $limit;

    /** @var string */
    public $orderby;

    /** @var string */
    public $order;
}
