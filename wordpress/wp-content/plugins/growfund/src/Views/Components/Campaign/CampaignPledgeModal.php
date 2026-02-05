<?php

namespace Growfund\Views\Components\Campaign;

defined( 'ABSPATH' ) || exit;

use Growfund\View;
use Growfund\DTO\RewardDTO;
use Growfund\DTO\Campaign\CampaignDTO;

class CampaignPledgeModal extends View {

    /** @var CampaignDTO */
    public $campaign;

    /** @var RewardDTO[] */
    public $rewards;

	protected function get_template_dir() {
        return 'site/components/campaign';
    }

    protected function enqueue_styles()
    {
        $main_styles_url = GROWFUND_DIR_URL . 'resources/assets/site/styles/components/campaign/campaign-pledge-modal.css';

        wp_enqueue_style(
                'growfund-campaign-pledge-modal-styles',
                $main_styles_url,
                ['growfund-main-styles'],
                GROWFUND_VERSION
            );
    }

    public $casts = [
        'campaign' => CampaignDTO::class,
        'rewards.*' => RewardDTO::class,
    ];
}
