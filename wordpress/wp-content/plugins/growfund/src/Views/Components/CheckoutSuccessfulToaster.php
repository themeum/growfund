<?php

namespace Growfund\Views\Components;

use Growfund\DTO\Pledge\PledgeDTO;
use Growfund\View;

defined('ABSPATH') || exit;

class CheckoutSuccessfulToaster extends View {
    /** @var string */
    public $svg_icon;

    /** @var PledgeDTO */
    public $pledge;

    /** @var string */
    public $confirmation_title;

    /** @var string */
    public $confirmation_description;

    protected function get_template_dir() {
        return 'site/components';
    }

    protected function enqueue_styles() {
        wp_enqueue_style(
            'growfund-checkout-successful-toaster-styles',
            GROWFUND_DIR_URL . 'resources/assets/site/styles/components/checkout-successful-toaster.css',
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

    public $casts = [ 
        'pledge' => PledgeDTO::class
    ];
}
