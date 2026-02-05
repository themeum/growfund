<?php

namespace Growfund\Views\Components\UI;

use Growfund\View; 

defined('ABSPATH') || exit;

class MediaSlider extends View
{
    /** @var array|null */
    public $images;

    /** @var array|null */
    public $video;

    protected function get_template_dir()
    {
        return 'site/components/ui';
    }

    protected function enqueue_scripts()
    {
        $script_url = GROWFUND_DIR_URL . 'resources/assets/site/scripts/components/ui/media-slider.js';
        wp_enqueue_script('growfund-media-slider-script', $script_url, ['growfund-core'], GROWFUND_VERSION, true);
    }
    protected function enqueue_styles()
    {
        $main_styles_url = GROWFUND_DIR_URL . 'resources/assets/site/styles/components/ui/media-slider.css';

        wp_enqueue_style(
                'growfund-media-slider-styles',
                $main_styles_url,
                ['growfund-main-styles'],
                GROWFUND_VERSION
            );
    }
}
