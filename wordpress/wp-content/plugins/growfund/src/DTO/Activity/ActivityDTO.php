<?php

namespace Growfund\DTO\Activity;

defined( 'ABSPATH' ) || exit;

use Growfund\DTO\DTO;

class ActivityDTO extends DTO
{
    /** @var \Growfund\Constants\ActivityType */
    public $type;

    /** @var int */
    public $campaign_id;

    /** @var int */
    public $pledge_id;

    /** @var int */
    public $donation_id;

    /** @var string */
    public $data;

    /** @var int */
    public $user_id;

    /** @var int */
    public $created_by;

    /** @var string */
    public $created_at;
}
