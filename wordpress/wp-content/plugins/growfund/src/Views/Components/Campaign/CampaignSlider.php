<?php

namespace Growfund\Views\Components\Campaign;

use Growfund\DTO\Campaign\CampaignDTO;
use Growfund\View;

defined('ABSPATH') || exit;

class CampaignSlider extends View
{
    /**
     * @var CampaignDTO[]
     */
    public $campaigns;

    /**
     * @var string
     */
    public $label;

    /**
     * @var string
     */
    public $id;

    /**
     * @var string
     */
    public $classname;

    /** @var bool */
    public $has_more = false;

    protected function get_template_dir()
    {
        return 'site/components/campaign';
    }

    protected function enqueue_scripts()
    {
        wp_enqueue_script(
            'growfund-campaign-slider-script',
            GROWFUND_DIR_URL . 'resources/assets/site/scripts/components/campaign/campaign-slider.js',
            ['growfund-core'],
            GROWFUND_VERSION,
            true
        );
    }

    protected function enqueue_styles()
    {
        wp_enqueue_style(
            'growfund-campaign-slider-styles',
            GROWFUND_DIR_URL . 'resources/assets/site/styles/components/campaign/campaign-slider.css',
            ['growfund-main-styles'],
            GROWFUND_VERSION
        );
    }

    public function get_casts()
    {
        return [
            'campaigns.*' => CampaignDto::class,
        ];
    }
}
