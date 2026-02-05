<?php 

namespace Growfund\Views\Pages;

defined( 'ABSPATH' ) || exit;

use Growfund\DTO\Campaign\CampaignDTO;
use Growfund\Payments\DTO\PaymentGatewayDTO;
use Growfund\Supports\Location;
use Growfund\View;


class DonationCheckoutPage extends View {
	/** @var CampaignDTO */
    public $campaign;

    /** @var \Growfund\Payments\DTO\PaymentGatewayDTO[] */
    public $payment_methods;

    /** @var array */
    public $funds;

    protected function get_template_dir() {
        return 'site/pages';
    }
    protected function enqueue_scripts()
    {
        growfund_localize_script('growfundCountries', Location::get_countries());
         
        $script_url = GROWFUND_DIR_URL . 'resources/assets/site/scripts/pages/donation-checkout-page.js';
        wp_enqueue_script('growfund-donation-checkout-page-script', $script_url, ['growfund-core'], GROWFUND_VERSION, true);
    }

    protected function enqueue_styles()
    {
        $main_styles_url = GROWFUND_DIR_URL . 'resources/assets/site/styles/pages/donation-checkout-page.css';

        wp_enqueue_style(
            'growfund-donation-checkout-styles',
            $main_styles_url,
            ['growfund-main-styles'],
            GROWFUND_VERSION
        );
    }

    public $casts = [
        'campaign' => CampaignDTO::class,
        'payment_methods.*' => PaymentGatewayDTO::class,
    ];
}
