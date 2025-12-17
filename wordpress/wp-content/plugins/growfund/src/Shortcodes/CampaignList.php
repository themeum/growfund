<?php

namespace Growfund\Shortcodes;

defined( 'ABSPATH' ) || exit;

use Growfund\Core\Shortcode;
use Growfund\DTO\Site\Campaign\CampaignFiltersDTO;
use Growfund\Services\Site\CampaignService;

class CampaignList extends Shortcode
{
    protected $name = 'growfund_campaign_list';

    /**
     * supported attributes
     * - header = list header
     * - category = category slug
     * - tag = campaign tags
     * - status = published, completed, funded (can use multiple status separated by comma)
     * - sort = newest or end_date
     * - start_date = campaign start date
     * - end_date = campaign end date
     * - limit = any positive integer number
     * - featured = true or false
     * - only_active = true or false
     */
    public function callback($shortcode_attr, string $content = '', string $shortcode_tag = '')
    {
        $campaign_service = new CampaignService();
        if (isset($shortcode_attr['status'])) {
            $shortcode_attr['status'] = explode(',', $shortcode_attr['status']);
        }
        $filters_dto = CampaignFiltersDTO::from_array($shortcode_attr);
        $campaigns = $campaign_service->paginated($filters_dto);

        $html = growfund_renderer()
                        ->get_html('site.components.campaign-list', [
                            'campaigns' => $campaigns,
                            'show_header' => false
                        ]);
        return '<div class="growfund-page-container">' . $html . '</div>';
    }
}
