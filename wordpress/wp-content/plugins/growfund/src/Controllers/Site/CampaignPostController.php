<?php

namespace Growfund\Controllers\Site;

defined( 'ABSPATH' ) || exit;

use Growfund\Contracts\Request;
use Growfund\DTO\CampaignPost\CampaignPostFilterDTO;
use Growfund\DTO\JsonResponseDTO;
use Growfund\Services\CampaignPostService;
use Growfund\Views\Components\CampaignUpdate\UpdateDetail;
use Growfund\Views\Components\CampaignUpdate\UpdateList;

/**
 * Class CampaignPostController
 * @since 1.0.0
 */
class CampaignPostController
{
    /**
     * @var CampaignPostService
     */
    protected $service;

    /**
     * Initialize the controller with CampaignPostService.
     */
    public function __construct()
    {
        $this->service = new CampaignPostService();
    }

    public function paginated_campaign_post_updates(Request $request) 
    {
        $filters_dto = new CampaignPostFilterDTO();
        $filters_dto->page = $request->get_int('page', 1);
        $filters_dto->limit = 6;
        $filters_dto->campaign_id = $request->get_int('campaign_id');

        $paginated = $this->service->paginated($filters_dto);

        $update_collection = new UpdateList();
		$update_collection->updates = $paginated->results;

        $response_dto = new JsonResponseDTO([
            'html' => growfund_get_safe_html($update_collection),
            'data' => $paginated,
        ]);

        return growfund_site_response()->json($response_dto);
    }

    public function get_campaign_post_update_detail(Request $request) 
    {
        $campaign_update_id = $request->get_int('id');

        $campaign_update = $this->service->get_by_id($campaign_update_id);
       
		$neighbors = $this->service->get_neighbors($campaign_update);

        $campaign_update_detail = new UpdateDetail();
        $campaign_update_detail->update = $campaign_update;

        $response_dto = new JsonResponseDTO([
            'html' => growfund_get_safe_html($campaign_update_detail),
            'data' => [
                'detail' => $campaign_update,
                'next_id' => $neighbors['next_id'],
				'prev_id' => $neighbors['prev_id']
            ],
        ]);

        return growfund_site_response()->json($response_dto);
    }
}
