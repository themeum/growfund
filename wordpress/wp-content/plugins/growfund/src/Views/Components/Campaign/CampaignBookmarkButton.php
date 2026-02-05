<?php

namespace Growfund\Views\Components\Campaign;

use Growfund\View;
use Growfund\DTO\Campaign\CampaignDTO;

defined('ABSPATH') || exit;

class CampaignBookmarkButton extends View
{
    /** @var CampaignDto */
    public $campaign;

    /** @var bool */
    public $has_label = true;

    /** @var string */
    public $id;

    /** @var string */
    public $classname;

    protected function get_template_dir()
    {
        return 'site/components/campaign';
    }

	protected function enqueue_styles()
    {
        wp_enqueue_style(
			'growfund-campaign-bookmark-button-styles',
			GROWFUND_DIR_URL . 'resources/assets/site/styles/components/campaign/campaign-bookmark-button.css',
			['growfund-main-styles'],
			GROWFUND_VERSION
        );
    }

    protected function enqueue_scripts()
    {
        wp_enqueue_script(
            'growfund-campaign-bookmark-button-script',
            GROWFUND_DIR_URL . 'resources/assets/site/scripts/components/campaign/campaign-bookmark-button.js',
            ['growfund-core'],
            GROWFUND_VERSION,
            true
        );
    }

    public function get_casts()
    {
        return [
            'campaign' => CampaignDto::class,
        ];
    }
}
