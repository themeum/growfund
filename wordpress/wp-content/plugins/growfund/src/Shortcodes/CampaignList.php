<?php

namespace Growfund\Shortcodes;

defined( 'ABSPATH' ) || exit;

use Growfund\Core\Shortcode;
use Growfund\DTO\Campaign\CampaignFiltersDTO;
use Growfund\Services\CampaignService;
use Growfund\Views\Components\Campaign\CampaignList as CampaignListView;

class CampaignList extends Shortcode
{
    protected $name = 'growfund_campaign_list';

    /**
     * supported attributes
     * - category_slug = category slug
     * - status = all, launched, launched-and-beyond, published, completed, funded
     * - order = asc or desc
     * - orderby = created_date, start_date, end_date
     * - limit = any positive integer number
     * - page = any positive integer number
     * - is_featured = true or false
     * - post_ids = comma separated post ids
     * - author_id = campaign creator id
     * - start_date = start date in 'Y-m-d' format -- filter by start date
     * - end_date = end date in 'Y-m-d' format -- filter by end date
     */
    public function callback($shortcode_attr, string $content = '', string $shortcode_tag = '')
    {
        $campaign_service = new CampaignService();

        if (isset($shortcode_attr['post_ids'])) {
            $shortcode_attr['post_ids'] = explode(',', $shortcode_attr['post_ids']);
        }

        $filters_dto = CampaignFiltersDTO::from_array($shortcode_attr);
        $paginated = $campaign_service->paginated($filters_dto);

        $campaign_list = new CampaignListView();
        $campaign_list->campaigns = $paginated->results;

        return growfund_get_html($campaign_list);
    }
}
