<?php

namespace Growfund\Views\Components\UI;

use Growfund\View;

defined('ABSPATH') || exit;

class InfiniteScroll extends View
{
    /** @var string */
    public $id;

    protected function get_template_dir()
    {
        return 'site/components/ui';
    }

    protected function enqueue_scripts()
    {
        wp_enqueue_script(
            'growfund-infinite-scroll-script',
            GROWFUND_DIR_URL . 'resources/assets/site/scripts/components/ui/infinite-scroll.js',
            ['growfund-core'],
            GROWFUND_VERSION,
            true
        );
    }

    protected function enqueue_styles() {
        wp_enqueue_style(
            'growfund-infinite-scroll-styles',
            GROWFUND_DIR_URL . 'resources/assets/site/styles/components/ui/infinite-scroll.css',
            ['growfund-main-styles'],
            GROWFUND_VERSION
        );
    }
}
