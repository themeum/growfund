<?php

namespace Growfund\Views\Components\Campaign;

defined( 'ABSPATH' ) || exit;

use Growfund\View;
use Growfund\DTO\RewardDTO;
use Growfund\DTO\Campaign\CampaignDTO;

class CampaignRewardContent extends View {
    /** @var RewardDTO */
    public $reward;

    /** @var CampaignDTO */
    public $campaign;

	/**
	 * @var string */
    public $classname;
    
	protected function get_template_dir() {
        return 'site/components/campaign';
    }

    protected function enqueue_styles()
    {
        $main_styles_url = GROWFUND_DIR_URL . 'resources/assets/site/styles/components/campaign/campaign-reward-content.css';

        wp_enqueue_style(
                'growfund-campaign-reward-content-styles',
                $main_styles_url,
                ['growfund-main-styles'],
                GROWFUND_VERSION
            );
    }

    public $casts = [
        'campaign' => CampaignDTO::class,
        'reward' => RewardDTO::class
    ];
}
