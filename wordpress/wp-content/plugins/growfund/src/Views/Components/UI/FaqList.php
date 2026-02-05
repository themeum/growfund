<?php

namespace Growfund\Views\Components\UI;

use Growfund\View;

defined('ABSPATH') || exit;

class FaqList extends View {
    /** * The array of FAQ items
     * @var array 
     */
    public $faqs;

    /** * The index of the default open FAQ item
     * @var int|null 
     */
    public $default_open_index;


    protected function get_template_dir() {
        return 'site/components/ui/';
    }

	protected function enqueue_scripts() {
       
        wp_enqueue_script(
			'growfund-faq-ui-scripts',
			GROWFUND_DIR_URL . 'resources/assets/site/scripts/components/ui/faq-list.js',
			['growfund-core'],
			GROWFUND_VERSION,
			true
        );
    }

    protected function enqueue_styles() {
        wp_enqueue_style(
            'growfund-faq-ui-styles',
            GROWFUND_DIR_URL . 'resources/assets/site/styles/components/ui/faq-list.css',
            ['growfund-main-styles'],
            GROWFUND_VERSION
        );
    }
}
