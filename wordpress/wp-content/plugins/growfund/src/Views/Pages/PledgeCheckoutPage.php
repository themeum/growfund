<?php 

namespace Growfund\Views\Pages;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\HookNames;
use Growfund\DTO\Campaign\CampaignDTO;
use Growfund\DTO\RewardDTO;
use Growfund\Payments\DTO\PaymentGatewayDTO;
use Growfund\Supports\Location;
use Growfund\View;


class PledgeCheckoutPage extends View {

    /** @var CampaignDTO */
    public $campaign;

    /** @var RewardDTO|null */
    public $reward;

    /** @var \Growfund\Payments\DTO\PaymentGatewayDTO[] */
    public $payment_methods;
    
    protected function get_template_dir() {
        return 'site/pages';
    }
    protected function enqueue_scripts()
    {
        growfund_localize_script('growfundCountries', Location::get_countries());

        $script_url = GROWFUND_DIR_URL . 'resources/assets/site/scripts/pages/pledge-checkout-page.js';
        wp_enqueue_script('growfund-pledge-checkout-page-script', $script_url, ['growfund-core'], GROWFUND_VERSION, true);
    }

    protected function enqueue_styles()
    {
        $main_styles_url = GROWFUND_DIR_URL . 'resources/assets/site/styles/pages/pledge-checkout-page.css';

        wp_enqueue_style(
                'growfund-pledge-checkout-styles',
                $main_styles_url,
                ['growfund-main-styles'],
                GROWFUND_VERSION
            );
    }


    public $casts = [
        'campaign' => CampaignDTO::class,
        'reward'   => RewardDTO::class,
        'payment_methods.*' => PaymentGatewayDTO::class
    ];
}
