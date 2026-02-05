<?php

namespace Growfund\Views\Components\Campaign;

use Growfund\DTO\Campaign\CampaignDTO;
use Growfund\View;

defined( 'ABSPATH' ) || exit;

class CampaignCard extends View {

    /** @var CampaignDTO */
    public $campaign;
    
    protected function get_template_dir() {
        return 'site/components/campaign';
    }

    protected function enqueue_styles()
    {
        $main_styles_url = GROWFUND_DIR_URL . 'resources/assets/site/styles/components/campaign/campaign-card.css';

        wp_enqueue_style(
                'growfund-campaign-card-styles',
                $main_styles_url,
                ['growfund-main-styles'],
                GROWFUND_VERSION
            );
    }

    public function get_casts()
    {
        return [
            'campaign' => CampaignDto::class,
        ];
    }
}
