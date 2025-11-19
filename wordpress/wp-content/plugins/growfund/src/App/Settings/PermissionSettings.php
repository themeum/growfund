<?php

namespace Growfund\App\Settings;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\AppConfigKeys;
use Growfund\Core\AppSettings;
use Growfund\Supports\Option;

class PermissionSettings extends AppSettings
{
    /**
     * Constructor - Initialize campaign settings from options.
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->settings = Option::get(AppConfigKeys::USER_PERMISSIONS, null);
    }

    public function refresh()
    {
        $this->settings = Option::get(AppConfigKeys::USER_PERMISSIONS, null);

        return $this;
    }

    /**
     * Check if anonymous donation is allowed.
     *
     * @return bool True if anonymous donation is allowed, false otherwise.
     * @since 1.0.0
     */
    public function allow_anonymous_donation()
    {
        return isset($this->settings['allow_anonymous_contributions'])
            ? filter_var($this->settings['allow_anonymous_contributions'], FILTER_VALIDATE_BOOLEAN)
            : false;
    }

    public function allow_contributor_comments()
    {
        return isset($this->settings['allow_contributor_comments'])
            ? filter_var($this->settings['allow_contributor_comments'], FILTER_VALIDATE_BOOLEAN)
            : false;
    }

    public function fundraisers_can_delete_campaigns()
    {
        return isset($this->settings['fundraisers_can_delete_campaigns'])
            ? filter_var($this->settings['fundraisers_can_delete_campaigns'], FILTER_VALIDATE_BOOLEAN)
            : false;
    }

    public function fundraisers_can_publish_campaigns()
    {
        return isset($this->settings['fundraisers_can_publish_campaigns'])
            ? filter_var($this->settings['fundraisers_can_publish_campaigns'], FILTER_VALIDATE_BOOLEAN)
            : false;
    }
}
