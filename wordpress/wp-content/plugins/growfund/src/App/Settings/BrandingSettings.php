<?php

namespace Growfund\App\Settings;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\AppConfigKeys;
use Growfund\Core\AppSettings;
use Growfund\Supports\Option;

class BrandingSettings extends AppSettings
{
    /**
     * Constructor - Initialize campaign settings from options.
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->settings = Option::get(AppConfigKeys::BRANDING, null);
    }

    public function refresh()
    {
        $this->settings = Option::get(AppConfigKeys::BRANDING, null);

        return $this;
    }

	public function get_logo($key = null)
    {
        return $this->settings['logo'][$key] ?? null;
    }

    public function get_logo_height()
    {
        return $this->settings['logo_height'] ?? null;
    }
}
