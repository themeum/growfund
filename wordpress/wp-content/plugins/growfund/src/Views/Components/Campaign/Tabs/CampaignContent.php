<?php

namespace Growfund\Views\Components\Campaign\Tabs;

defined('ABSPATH') || exit;

use Growfund\View;
use Growfund\DTO\Campaign\CampaignDTO;
use Growfund\DTO\RewardDTO;

class CampaignContent extends View {
    /**
     * @var CampaignDTO
     */
    public $campaign;

    /** @var RewardDTO[] */
    public $rewards;

    /**
     * Define the template directory
     */
    protected function get_template_dir() {
        return 'site/components/campaign/tabs/';
    }
    protected function enqueue_styles() {
        wp_enqueue_style(
            'growfund-campaign-tab-content-styles',
            GROWFUND_DIR_URL . 'resources/assets/site/styles/components/campaign/tabs/campaign-tab-content.css',
            ['growfund-main-styles'],
            GROWFUND_VERSION
        );
    }

    public $casts = [
        'campaign' => CampaignDTO::class,
        'rewards.*' => RewardDTO::class
    ];
}
