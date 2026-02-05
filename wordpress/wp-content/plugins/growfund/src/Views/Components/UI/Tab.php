<?php

namespace Growfund\Views\Components\UI;

use Growfund\View;

defined('ABSPATH') || exit;

class Tab extends View
{
    /**
     * @var array
     */
    public $tabs;
    
    /** @var bool $allow_header_button */
    public $allow_header_button = false;

    /** @var View|null $header_button */
    public $header_button; 

    /** @var string */
    public $id;


    protected function get_template_dir()
    {
        return 'site/components/ui';
    }

    protected function enqueue_scripts()
    {
        wp_enqueue_script(
            'growfund-tab-script',
            GROWFUND_DIR_URL . 'resources/assets/site/scripts/components/ui/tab.js',
            ['growfund-core'],
            GROWFUND_VERSION,
            true
        );
    }

    protected function enqueue_styles()
    {
        wp_enqueue_style(
            'growfund-tab-styles',
            GROWFUND_DIR_URL . 'resources/assets/site/styles/components/ui/tab.css',
            ['growfund-main-styles'],
            GROWFUND_VERSION
        );
    }
}
