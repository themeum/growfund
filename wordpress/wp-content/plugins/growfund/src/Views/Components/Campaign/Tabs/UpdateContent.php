<?php

namespace Growfund\Views\Components\Campaign\Tabs;

use Growfund\DTO\CampaignPost\CampaignPostDTO;
use Growfund\View;

defined('ABSPATH') || exit;

class UpdateContent extends View {
    /** @var string */
    public $campaign_id;

    /** @var CampaignPostDTO[] */
    public $updates = []; 

    /** @var string svg file path */
    public $svg_icon;

    /** @var string */
    public $id;



    protected function get_template_dir() {
        return 'site/components/campaign/tabs/';
    }

    protected function enqueue_scripts() {
       
        wp_enqueue_script(
			'growfund-update-tab-content',
			GROWFUND_DIR_URL . 'resources/assets/site/scripts/components/campaign/tabs/update-tab-content.js',
			['growfund-core'],
			GROWFUND_VERSION,
			true
        );
    }

    protected function enqueue_styles() {
        wp_enqueue_style(
            'growfund-update-tab-content-styles',
            GROWFUND_DIR_URL . 'resources/assets/site/styles/components/campaign/tabs/update-tab-content.css',
            ['growfund-main-styles'],
            GROWFUND_VERSION
        );
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
        'updates.*' => CampaignPostDTO::class
    ];
}
