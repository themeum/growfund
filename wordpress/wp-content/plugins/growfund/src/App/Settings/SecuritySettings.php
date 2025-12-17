<?php

namespace Growfund\App\Settings;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\AppConfigKeys;
use Growfund\Constants\HookNames;
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
        return apply_filters(HookNames::GROWFUND_ALLOW_EMAIL_VERIFICATION, false, $this->settings);
    }
}
