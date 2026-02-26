<?php

namespace Growfund\Views\Components\Donors;

use Growfund\DTO\Campaign\CampaignDTO;
use Growfund\DTO\Donor\DonorDisplayDTO;
use Growfund\View;

defined('ABSPATH') || exit;

class DonorList extends View
{
	/**
    * @var DonorDisplayDTO[]
    */
    public $donors;

    /** @var CampaignDTO */

    public $campaign;

    /** @var string */
    public $svg_icon;

    public $casts = [
        'donors.*' => DonorDisplayDTO::class,
        'campaign' => CampaignDTO::class,
    ];

    protected function get_template_dir()
    {
        return 'site/components/donors';
    }
	protected function enqueue_scripts()
    {
        $script_url = GROWFUND_DIR_URL . 'resources/assets/site/scripts/components/donors/donor-list.js';
        wp_enqueue_script('growfund-donor-list-script', $script_url, ['growfund-core'], GROWFUND_VERSION, true);
    }
    

    protected function enqueue_styles()
	{
		wp_enqueue_style(
        'growfund-donor-list-styles',
        GROWFUND_DIR_URL . 'resources/assets/site/styles/components/donors/donor-list.css',
        ['growfund-main-styles'],
        GROWFUND_VERSION
		);

		wp_enqueue_style(
        'growfund-donor-item-styles',
        GROWFUND_DIR_URL . 'resources/assets/site/styles/components/donors/donor-item.css',
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
}
