<?php 

namespace Growfund\Views\Pages;

defined( 'ABSPATH' ) || exit;

use Growfund\DTO\Campaign\CampaignDTO;
use Growfund\DTO\PaginatedCollectionDTO;
use Growfund\View;


class CampaignArchivePage extends View {
    /** @var string */
    public $banner_title;

    /** @var PaginatedCollectionDTO */
    public $featured_campaigns;

    /** @var PaginatedCollectionDTO */
    public $campaigns;

    /** @var array<{id:int,name:string}>*/
    public $categories;

    /** @var string|null */
    public $search;

    /** @var string|null */
    public $category_slug;

    /** @var string|null */
    public $orderby;

    /** @var string|'desc' -- asc|desc */
    public $order = 'desc';

    public function get_casts()
    {
        return [
            'featured_campaigns.results' => CampaignDTO::class,
            'campaigns.results' => CampaignDTO::class
        ];
    }

    protected function get_template_dir() {
        return 'site/pages';
    }
    protected function enqueue_scripts()
    {
        $script_url = GROWFUND_DIR_URL . 'resources/assets/site/scripts/pages/campaign-archive-page.js';
        wp_enqueue_script('growfund-campaign-archive-page-script', $script_url, ['growfund-core'], GROWFUND_VERSION, true);
    }

	protected function enqueue_styles()
    {
        $main_styles_url = GROWFUND_DIR_URL . 'resources/assets/site/styles/pages/campaign-archive-page.css';

        wp_enqueue_style(
				'growfund-campaign-archive-page-styles',
				$main_styles_url,
				['growfund-main-styles'],
				GROWFUND_VERSION
			);
    }
}
