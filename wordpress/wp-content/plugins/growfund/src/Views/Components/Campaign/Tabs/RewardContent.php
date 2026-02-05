<?php

namespace Growfund\Views\Components\Campaign\Tabs;

use Growfund\DTO\RewardDTO;
use Growfund\View;
use Growfund\DTO\Campaign\CampaignDTO;

defined('ABSPATH') || exit;

class RewardContent extends View {
    /** @var CampaignDTO */
    public $campaign;

    /** @var RewardDTO[] */
    public $rewards;

    protected function get_template_dir() {
        return 'site/components/campaign/tabs/';
    }

	protected function enqueue_styles() {
        wp_enqueue_style(
			'growfund-reward-tab-content-styles',
			GROWFUND_DIR_URL . 'resources/assets/site/styles/components/campaign/tabs/reward-tab-content.css',
			['growfund-main-styles'],
			GROWFUND_VERSION
        );
    }

    public $casts = [
        'campaign' => CampaignDTO::class,
        'rewards.*' => RewardDTO::class
    ];
}
