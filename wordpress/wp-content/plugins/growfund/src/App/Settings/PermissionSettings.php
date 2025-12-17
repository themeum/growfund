<?php

namespace Growfund\App\Settings;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\AppConfigKeys;
use Growfund\Constants\HookNames;
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
        return apply_filters(HookNames::GROWFUND_ALLOW_ANONYMOUS_CONTRIBUTION_FILTER, false, $this->settings);
    }

    public function allow_contributor_comments()
    {
        return apply_filters(HookNames::GROWFUND_ALLOW_CONTRIBUTOR_COMMENTS_FILTER, false, $this->settings);
    }

    public function fundraisers_can_delete_campaigns()
    {
        return apply_filters(HookNames::GROWFUND_FUNDRAISER_CAMPAIGN_DELETION_FILTER, false, $this->settings);
    }

    public function fundraisers_can_publish_campaigns()
    {
        return apply_filters(HookNames::GROWFUND_FUNDRAISER_CAMPAIGN_PUBLISH_FILTER, false, $this->settings);
    }
}
