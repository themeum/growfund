<?php

namespace Growfund\Constants\Contributor;

defined( 'ABSPATH' ) || exit;

use Growfund\Traits\HasConstants;

class DisplayOption
{
    use HasConstants;

    const SHOW_AMOUNT_AND_NAME = 'show-amount-and-name';
    const SHOW_NAME = 'show-name';
    const SHOW_AMOUNT = 'show-amount';
}
