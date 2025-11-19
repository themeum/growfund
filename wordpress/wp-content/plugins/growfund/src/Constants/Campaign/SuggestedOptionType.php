<?php

namespace Growfund\Constants\Campaign;

defined( 'ABSPATH' ) || exit;

use Growfund\Traits\HasConstants;

class SuggestedOptionType
{
    use HasConstants;

    const AMOUNT_ONLY = 'amount-only';

    const AMOUNT_DESCRIPTION = 'amount-description';
}
