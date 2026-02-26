<?php

namespace Growfund\Constants\RewardItem;

defined( 'ABSPATH' ) || exit;

use Growfund\Traits\HasConstants;

class RewardItemType
{
    use HasConstants;

    const PHYSICAL = 'physical';
    const DIGITAL = 'digital';
}
