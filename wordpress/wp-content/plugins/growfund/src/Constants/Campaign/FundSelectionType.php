<?php

namespace Growfund\Constants\Campaign;

defined( 'ABSPATH' ) || exit;

use Growfund\Traits\HasConstants;

class FundSelectionType
{
    use HasConstants;

    const FIXED = 'fixed';

    const DONOR_DECIDE = 'donor-decide';
}
