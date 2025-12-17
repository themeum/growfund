<?php

namespace Growfund\Core;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\HookNames;

/**
 * @deprecated since 1.1.0
 */
class FeatureManager
{
    /**
     * The feature manager constructor.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * @deprecated since 1.1.0
     * Check if the pro plugin is activated.
     * Keep for backward compatibility
     * @return bool
     */
    public function is_pro() {
        return has_growfund_pro();
    }
}
