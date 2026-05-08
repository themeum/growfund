<?php

namespace Growfund\Constants;

defined( 'ABSPATH' ) || exit;

use Growfund\Traits\HasConstants;

class UserCustomRowAction
{
    use HasConstants;

    const GROWFUND_MAKE_FUNDRAISER = 'growfund_make_fundraiser';
    const GROWFUND_MAKE_DONOR = 'growfund_make_donor';
    const GROWFUND_MAKE_BACKER = 'growfund_make_backer';
}
