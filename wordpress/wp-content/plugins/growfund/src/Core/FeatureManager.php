<?php

namespace Growfund\Core;

defined( 'ABSPATH' ) || exit;


/**
 * @deprecated since 1.0.2
 * keep the class for backward compatibility. will be removed in version 1.1.0
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
     * @deprecated since 1.0.2
     * keep this method for backward compatibility. will be removed in version 1.1.0
     * 
     * Check if the pro plugin is activated.
     * @return bool
     */
    public function is_pro() {
        return has_growfund_pro();
    }
}
