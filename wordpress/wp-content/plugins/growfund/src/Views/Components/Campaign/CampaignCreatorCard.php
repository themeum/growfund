<?php

namespace Growfund\Views\Components\Campaign;

use Growfund\View;

defined( 'ABSPATH' ) || exit;

class CampaignCreatorCard extends View {
    /** @var string */
    public $display_name;

    /** @var string */
    public $avatar_src;

    /** @var string */
    public $avatar_class;

    /** @var int */
    public $total_campaign_created;

    /** @var int */
    public $total_number_of_contributions;

    protected function get_template_dir() {
        return 'site/components/campaign';
    }


    protected function enqueue_styles() {
        wp_enqueue_style(
            'growfund-campaign-creator-card-styles',
            GROWFUND_DIR_URL . 'resources/assets/site/styles/components/campaign/campaign-creator-card.css',
            ['growfund-main-styles'],
            GROWFUND_VERSION
        );
    }
}
