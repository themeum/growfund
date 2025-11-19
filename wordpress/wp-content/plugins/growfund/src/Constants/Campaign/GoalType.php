<?php

namespace Growfund\Constants\Campaign;

defined( 'ABSPATH' ) || exit;

use Growfund\Traits\HasConstants;

class GoalType
{
    use HasConstants;

    const RAISED_AMOUNT = 'raised-amount';

    const NO_OF_CONTRIBUTIONS = 'number-of-contributions';

    const NO_OF_CONTRIBUTORS = 'number-of-contributors';
}
