<?php

namespace Growfund\Constants;

defined( 'ABSPATH' ) || exit;

use Growfund\Traits\HasConstants;

class TransientKeys
{
    use HasConstants;

    /**
     * Transient key to store the whether the growfund plugin is activated
     */
    const GROWFUND_GROWFUND_ACTIVATED = 'growfund_growfund_activated_transient';
}
