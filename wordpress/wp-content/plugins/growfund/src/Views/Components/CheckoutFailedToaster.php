<?php

namespace Growfund\Views\Components;

use Growfund\View;

defined('ABSPATH') || exit;

class CheckoutFailedToaster extends View {

    /** @var string */
    public $svg_icon;

    /** @var string */
    public $campaign_id;

    protected function get_template_dir() {
        return 'site/components';
    }

    protected function enqueue_styles() {
        wp_enqueue_style(
            'growfund-checkout-failed-toaster-styles',
            GROWFUND_DIR_URL . 'resources/assets/site/styles/components/checkout-failed-toaster.css',
            ['growfund-main-styles'],
            GROWFUND_VERSION
        );
    }
    /** 
     * Get SVG icon markup
     * @return string
     */
    public function get_icon() {
        if ( ! $this->svg_icon ) {
            return '';
        }
        
        return $this->get_svg_icon($this->svg_icon);
    }
}
