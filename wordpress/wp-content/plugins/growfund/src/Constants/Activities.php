<?php

namespace Growfund\Constants;

defined( 'ABSPATH' ) || exit;

use Growfund\Traits\HasConstants;

class Activities
{
    use HasConstants;

    const PLEDGE = 'pledge';
    const DONATION = 'donation';
    const FUNDRAISER = 'fundraiser';
    const BACKER = 'backer';
    const DONOR = 'donor';
}
