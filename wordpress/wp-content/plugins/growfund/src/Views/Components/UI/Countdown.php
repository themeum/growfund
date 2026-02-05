<?php

namespace Growfund\Views\Components\UI;

use Growfund\View; 

defined('ABSPATH') || exit;

class Countdown extends View
{
    /** @var \DateTime */
    public $end_date;

    protected function get_template_dir()
    {
        return 'site/components/ui';
    }

    protected function enqueue_scripts()
    {
        $script_url = GROWFUND_DIR_URL . 'resources/assets/site/scripts/components/ui/countdown.js';
        wp_enqueue_script('growfund-countdown-script', $script_url, ['growfund-core'], GROWFUND_VERSION, true);
    }
    protected function enqueue_styles()
    {
        $main_styles_url = GROWFUND_DIR_URL . 'resources/assets/site/styles/components/ui/countdown.css';

        wp_enqueue_style(
                'growfund-countdown-styles',
                $main_styles_url,
                ['growfund-main-styles'],
                GROWFUND_VERSION
            );
    }
}
