<?php

namespace Growfund\Views\Components\Donors;

use Growfund\DTO\Donor\DonorDisplayDTO;
use Growfund\View;

defined('ABSPATH') || exit;

class DonorItem extends View
{

	/**
	 * @var DonorDisplayDTO
	 */
    public $donor;

    /** @var string */
    public $svg_icon;
  
	
    protected function get_template_dir()
    {
        return 'site/components/donors';
    }
    

    protected function enqueue_styles()
    {
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

    public function get_casts()
    {
        return [
            'donor' => DonorDisplayDTO::class,
        ];
    }
}
