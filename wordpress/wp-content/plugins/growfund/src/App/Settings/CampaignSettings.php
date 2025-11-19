<?php

namespace Growfund\App\Settings;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\AppConfigKeys;
use Growfund\Core\AppSettings;
use Growfund\Supports\Option;

class CampaignSettings extends AppSettings
{
    /**
     * Constructor - Initialize campaign settings from options.
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->settings = Option::get(AppConfigKeys::CAMPAIGN, null);
    }

    public function refresh()
    {
        $this->settings = Option::get(AppConfigKeys::CAMPAIGN, null);

        return $this;
    }

    /**
     * Check if login is required to view campaign details.
     *
     * @return bool True if login is required, false otherwise.
     * @since 1.0.0
     */
    public function is_login_required_to_view_campaign_detail()
    {
        return isset($this->settings['is_login_required_to_view_campaign_detail'])
            ? filter_var($this->settings['is_login_required_to_view_campaign_detail'], FILTER_VALIDATE_BOOLEAN)
            : false;
    }

    /**
     * Check if contributor list should be displayed publicly.
     *
     * @return bool True if contributor list is public, false otherwise.
     * @since 1.0.0
     */
    public function display_contributor_list_publicly()
    {
        return isset($this->settings['display_contributor_list_publicly'])
            ? filter_var($this->settings['display_contributor_list_publicly'], FILTER_VALIDATE_BOOLEAN)
            : false;
    }

    /**
     * Get campaign update visibility setting.
     *
     * @return string The visibility setting, defaults to 'public'.
     * @since 1.0.0
     */
    public function campaign_update_visibility()
    {
        return $this->settings['campaign_update_visibility'] ?? 'public';
    }

    /**
     * Get social sharing options.
     *
     * @return array Array of social sharing settings, defaults to empty array.
     * @since 1.0.0
     */
    public function social_shares()
    {
        return $this->settings['social_shares'] ?? [];
    }

    /**
     * Check if comments are allowed on campaigns.
     *
     * @return bool True if comments are allowed, false otherwise.
     * @since 1.0.0
     */
    public function allow_comments()
    {
        return isset($this->settings['allow_comments'])
            ? filter_var($this->settings['allow_comments'], FILTER_VALIDATE_BOOLEAN)
            : false;
    }

    /**
     * Get comment moderation setting.
     *
     * @return string The comment moderation setting, defaults to 'immediate'.
     * @since 1.0.0
     */
    public function comment_moderation()
    {
        return $this->settings['comment_moderation'] ?? 'immediate';
    }

    /**
     * Get comment visibility setting.
     *
     * @return string The comment visibility setting, defaults to 'public'.
     * @since 1.0.0
     */
    public function comment_visibility()
    {
        return $this->settings['comment_visibility'] ?? 'public';
    }

    /**
     * Check if tribute donations are allowed.
     *
     * @return bool True if tribute is allowed, false otherwise.
     * @since 1.0.0
     */
    public function allow_tribute()
    {
        return growfund_app_features()->is_pro() && isset($this->settings['allow_tribute'])
            ? filter_var($this->settings['allow_tribute'], FILTER_VALIDATE_BOOLEAN)
            : false;
    }

    /**
     * Check if funding is allowed for campaigns.
     *
     * @return bool True if funding is allowed, false otherwise.
     * @since 1.0.0
     */
    public function allow_fund()
    {
        return growfund_app_features()->is_pro() && isset($this->settings['allow_fund'])
            ? filter_var($this->settings['allow_fund'], FILTER_VALIDATE_BOOLEAN)
            : false;
    }
}
