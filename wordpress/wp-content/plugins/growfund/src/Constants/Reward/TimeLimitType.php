<?php

namespace Growfund\Constants\Reward;

defined( 'ABSPATH' ) || exit;

use Growfund\Traits\HasConstants;

class TimeLimitType
{
    use HasConstants;

    const NO_LIMIT = 'no-limit';
    const SPECIFIC_DATE = 'specific-date';
}
