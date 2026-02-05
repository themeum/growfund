<?php

namespace Growfund\Views\Components;

use Growfund\DTO\Donation\DonationDTO;
use Growfund\View;

defined('ABSPATH') || exit;

class PaymentSuccessToaster extends View {
    /** @var string */
    public $svg_icon;

    /** @var DonationDTO */
    public $donation;

    /** @var string */
    public $confirmation_title;

    /** @var string */
    public $confirmation_description;

    protected function get_template_dir() {
        return 'site/components';
    }

    protected function enqueue_styles() {
        wp_enqueue_style(
            'growfund-payment-success-toaster-styles',
            GROWFUND_DIR_URL . 'resources/assets/site/styles/components/payment-success-toaster.css',
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
        'donation' => DonationDTO::class
    ];
}
