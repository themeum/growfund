<?php

namespace Growfund\Views\Components\Campaign\Tabs;

use Growfund\View;

defined('ABSPATH') || exit;

class FaqContent extends View {
    /** * The array of FAQ items
     * @var array 
     */
    public $faqs;


    protected function get_template_dir() {
        return 'site/components/campaign/tabs/';
    }
    
    protected function enqueue_styles() {
        wp_enqueue_style(
            'growfund-faq-tab-content-styles',
            GROWFUND_DIR_URL . 'resources/assets/site/styles/components/campaign/tabs/faq-tab-content.css',
            ['growfund-main-styles'],
            GROWFUND_VERSION
        );
    }
}
