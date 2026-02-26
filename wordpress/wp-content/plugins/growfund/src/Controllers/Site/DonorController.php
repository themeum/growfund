<?php

namespace Growfund\Controllers\Site;

use Growfund\Constants\Contributor\DisplayOptionOrderBy;
use Growfund\Contracts\Request;
use Growfund\DTO\JsonResponseDTO;
use Growfund\Services\DonorService;
use Growfund\Views\Components\Donors\DonorCollection;

defined( 'ABSPATH' ) || exit;

class DonorController
{
    private $service;

    public function __construct()
    {
        $this->service = new DonorService();
    }

    public function show()
    {
        return growfund_renderer()->get_html('dashboard.app');
    }

    public function get_public_list(Request $request)
    {
        $paginated = $this->service->get_public_list_for_display(
            $request->get_int('campaign_id', 0), 
            $request->get_int('page', 1),
            $request->get_int('limit', 0),
            $request->get_int('sort_key', DisplayOptionOrderBy::RECENT_ONLY),
        );
        $donor_list = new DonorCollection();
        $donor_list->donors = $paginated->results ?? [];
        
        $response_dto = new JsonResponseDTO([
            'html' => growfund_get_safe_html($donor_list),
            'data' => $paginated,
        ]);
        
        return growfund_site_response()->json($response_dto);
    }
}
