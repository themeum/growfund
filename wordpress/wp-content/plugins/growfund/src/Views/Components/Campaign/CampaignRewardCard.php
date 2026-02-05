<?php

namespace Growfund\Views\Components\Campaign;

defined( 'ABSPATH' ) || exit;

use Growfund\View;
use Growfund\DTO\RewardDTO;
use Growfund\DTO\Campaign\CampaignDTO;


class CampaignRewardCard extends View {

    /** @var RewardDTO */
    public $reward;

    /** @var CampaignDTO */
    public $campaign;

	protected function get_template_dir() {
        return 'site/components/campaign';
    }

    protected function enqueue_styles()
    {
        $main_styles_url = GROWFUND_DIR_URL . 'resources/assets/site/styles/components/campaign/campaign-reward-card.css';

        wp_enqueue_style(
                'growfund-campaign-reward-card-styles',
                $main_styles_url,
                ['growfund-main-styles'],
                GROWFUND_VERSION
            );
    }

    public $casts = [
        'reward' => RewardDTO::class,
        'campaign' => CampaignDTO::class
    ];
}
