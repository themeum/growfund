<?php

namespace Growfund\App\Settings;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\AppConfigKeys;
use Growfund\Core\AppSettings;
use Growfund\Supports\Option;

class PageSettings extends AppSettings
{
    /**
     * Constructor - Initialize campaign settings from options.
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->settings = Option::get(AppConfigKeys::PAGE, null);
    }

    public function refresh()
    {
        $this->settings = Option::get(AppConfigKeys::PAGE, null);

        return $this;
    }

    /**
     * Get login page id.
     * 
     * @return int
     */
    public function get_login_page_id()
    {
        return (int) ($this->settings['login_page'] ?? 0);
    }

    /**
     * Get registration page id.
     * 
     * @return int
     */
    public function get_registration_page_id()
    {
        return (int) ($this->settings['registration_page'] ?? 0);
    }

    /**
     * Get fundraiser registration page id.
     * 
     * @return string
     */
    public function get_fundraiser_registration_page_id()
    {
        return (int) ($this->settings['fundraiser_registration_page'] ?? 0);
    }

    /**
     * Get campaigns page id.
     * 
     * @return int
     */
    public function get_campaigns_page_id()
    {
        return (int) ($this->settings['campaigns_page'] ?? 0);
    }

    /**
     * Get checkout page id.
     * 
     * @return int
     */
    public function get_checkout_page_id()
    {
        return (int) ($this->settings['checkout_page'] ?? 0);
    }

    /**
     * Get privacy policy page id.
     * 
     * @return int
     */
    public function get_privacy_policy_page_id()
    {
        return (int) ($this->settings['privacy_policy_page'] ?? 0);
    }

    /**
     * Get terms and conditions page id.
     * 
     * @return int
     */
    public function get_terms_and_conditions_page_id()
    {
        return (int) ($this->settings['terms_and_conditions_page'] ?? 0);
    }
}
