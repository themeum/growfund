<?php

namespace Growfund\Views\Components\Donors;

use Growfund\DTO\Donor\DonorDisplayDTO;
use Growfund\View;

defined('ABSPATH') || exit;

class DonorCollection extends View
{
    /**
     * @var DonorDisplayDTO[]
     */
    public $donors;

    /**
     * @var string 
     */
    public $svg_icon;

    public $casts = [
        'donors.*' => DonorDisplayDTO::class
    ];

    /**
     * Define the template directory path
     * @return string
     */
    protected function get_template_dir()
    {
        return 'site/components/donors';
    }

    protected function enqueue_styles()
    {
        wp_enqueue_style(
            'growfund-donor-collection-styles',
            GROWFUND_DIR_URL . 'resources/assets/site/styles/components/donors/donor-collection.css',
            ['growfund-main-styles'],
            GROWFUND_VERSION
        );
    }

    /** * Retrieves the SVG markup using the provided icon name
     * @return string
     */
    public function get_icon() 
    {
        if ( ! $this->svg_icon ) {
            return '';
        }
        
        return $this->get_svg_icon($this->svg_icon);
    }
}
