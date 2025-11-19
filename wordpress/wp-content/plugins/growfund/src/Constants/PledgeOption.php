<?php

namespace Growfund\Constants;

defined( 'ABSPATH' ) || exit;

use Growfund\Traits\HasConstants;

class PledgeOption
{
    use HasConstants;

    const WITH_REWARDS = 'with-rewards';

    const WITHOUT_REWARDS = 'without-rewards';
}
