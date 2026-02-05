<?php

namespace Growfund\Views\Components\Campaign;

use Growfund\View;

defined('ABSPATH') || exit;

class CampaignFilter extends View
{
    /** @var array<{id:int,slug:string,name:string}>*/
    public $categories;

    /** @var string|null */
    public $search;

    /** @var string|null */
    public $category_slug;

    /** @var string|null */
    public $orderby;

    /** @var string|'desc' -- asc|desc */
    public $order = 'desc';

    protected function get_template_dir()
    {
        return 'site/components/campaign';
    }

    protected function enqueue_scripts()
    {
        wp_enqueue_script(
            'growfund-campaign-filter-script',
            GROWFUND_DIR_URL . 'resources/assets/site/scripts/components/campaign/campaign-filter.js',
            ['growfund-core'],
            GROWFUND_VERSION,
            true
        );
    }

    protected function enqueue_styles()
    {
        wp_enqueue_style(
            'growfund-campaign-filter-styles',
            GROWFUND_DIR_URL . 'resources/assets/site/styles/components/campaign/campaign-filter.css',
            ['growfund-main-styles'],
            GROWFUND_VERSION
        );
    }
}
