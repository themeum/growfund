<?php

namespace Growfund\Constants\Reward;

defined( 'ABSPATH' ) || exit;

use Growfund\Traits\HasConstants;

class RewardType
{
    use HasConstants;

    const PHYSICAL_GOODS = 'physical-goods';
    const DIGITAL_GOODS = 'digital-goods';
    const PHYSICAL_AND_DIGITAL_GOODS = 'physical-and-digital-goods';
}
