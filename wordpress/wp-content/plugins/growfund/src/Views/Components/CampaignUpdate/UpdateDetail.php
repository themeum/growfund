<?php

namespace Growfund\Views\Components\CampaignUpdate;

use Growfund\DTO\CampaignPost\CampaignPostDTO;
use Growfund\View;

defined('ABSPATH') || exit;

class UpdateDetail extends View {

    /** @var string svg file path */
    public $svg_icon;

        /** @var string */
    public $id;

        /** @var CampaignPostDTO */
    public $update;

    protected function get_template_dir() {
        return 'site/components/campaign-update';
    }

    protected function enqueue_styles() {
        wp_enqueue_style(
            'growfund-update-detail-styles',
            GROWFUND_DIR_URL . 'resources/assets/site/styles/components/campaign-update/update-detail.css',
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
        'update' => CampaignPostDTO::class
    ];
}
