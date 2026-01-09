<?php

namespace Growfund\App\Settings;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\AppConfigKeys;
use Growfund\Core\AppSettings;
use Growfund\Parsers\ShortcodeParser;
use Growfund\Supports\Option;
use Growfund\Supports\AdminUser;

class GeneralSettings extends AppSettings
{
    /**
     * Constructor - Initialize campaign settings from options.
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->settings = Option::get(AppConfigKeys::GENERAL, null);
    }

    public function refresh()
    {
        $this->settings = Option::get(AppConfigKeys::GENERAL, null);

        return $this;
    }

    /**
     * Get organization name.
     * 
     * @return string
     */
    public function get_organization_name()
    {
        return $this->settings['organization']['name'] ?? growfund_site_name();
    }


    /**
     * Get organization location.
     * 
     * @return string
     */
    public function get_organization_location()
    {
        return $this->settings['organization']['location'] ?? '';
    }


    /**
     * Get organization contact email.
     * 
     * @return string
     */
    public function get_organization_contact_email()
    {
        return $this->settings['organization']['contact_email'] ?? AdminUser::get_email();
    }

    public function get_tnc_text() {
        $parser = new ShortcodeParser();
        
        return $parser->with([
            'privacy_policy_page' => growfund_renderer()->get_html('mails.components.links.privacy-policy'),
            'terms_and_conditions_page' => growfund_renderer()->get_html('mails.components.links.terms-and-conditions'),
        ])->parse($this->settings['tnc_text'] ?? '');
    }
}
