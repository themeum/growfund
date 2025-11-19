<?php

namespace Growfund\Constants;

defined( 'ABSPATH' ) || exit;

use Growfund\Traits\HasConstants;

class URLs
{
    use HasConstants;

    /**
     * Default checkout URL pattern
     */
    const DEFAULT_CHECKOUT_URL = 'campaign/checkout';
}
