<?php

namespace Growfund\App\Settings;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\AppConfigKeys;
use Growfund\Core\AppSettings;
use Growfund\Supports\Option;

class ReceiptSettings extends AppSettings
{
    /**
     * Constructor - Initialize campaign settings from options.
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->settings = Option::get(AppConfigKeys::PDF_RECEIPT, null);
    }

    public function refresh()
    {
        $this->settings = Option::get(AppConfigKeys::PDF_RECEIPT, null);

        return $this;
    }
}
