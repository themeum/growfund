<?php

namespace Growfund\App\Settings;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\AppConfigKeys;
use Growfund\Core\AppSettings;
use Growfund\Supports\Option;

class EmailAndNotificationSettings extends AppSettings
{
    /**
     * Constructor - Initialize campaign settings from options.
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->settings = Option::get(AppConfigKeys::EMAIL_AND_NOTIFICATIONS, null);
    }

    public function refresh()
    {
        $this->settings = Option::get(AppConfigKeys::EMAIL_AND_NOTIFICATIONS, null);

        return $this;
    }
}
