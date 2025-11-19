<?php

namespace Growfund\App\Settings;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\AppConfigKeys;
use Growfund\Core\AppSettings;
use Growfund\Supports\Option;

class SecuritySettings extends AppSettings
{
    /**
     * Constructor - Initialize campaign settings from options.
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->settings = Option::get(AppConfigKeys::SECURITY, null);
    }

    public function refresh()
    {
        $this->settings = Option::get(AppConfigKeys::SECURITY, null);

        return $this;
    }

    /**
     * Check if email verification is enabled.
     * @return bool
     */
    public function is_enabled_email_verification()
    {
        return isset($this->settings['is_enabled_email_verification'])
            ? filter_var($this->settings['is_enabled_email_verification'], FILTER_VALIDATE_BOOLEAN)
            : false;
    }
}
