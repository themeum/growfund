<?php

namespace Growfund\Controllers\Site;

defined( 'ABSPATH' ) || exit;

use Growfund\Contracts\Request;
use Growfund\Services\CampaignService;
use Growfund\Services\RewardService;
use Growfund\DTO\JsonResponseDTO;
use Growfund\Views\Components\Campaign\RewardCollection;

class RewardController
{
    /**
     * @var RewardService
     */
    private $service;

    /**
     * @var CampaignService
     */
    private $campaign_service;

    public function __construct() {
        $this->service = new RewardService();
        $this->campaign_service = new CampaignService();
    }


    public function get_campaign_rewards(Request $request) {
        $campaign_id = $request->get_int('campaign_id');

        $campaign = $this->campaign_service->get_by_id($campaign_id);
        $rewards = $this->service->get_all($campaign_id);

        $reward_collection = new RewardCollection();
        $reward_collection->rewards = $rewards;
        $reward_collection->campaign = $campaign;

        $response_dto = new JsonResponseDTO([
            'html' => growfund_get_safe_html($reward_collection),
            'data' => $rewards,
        ]);

        return growfund_site_response()->json($response_dto);
    }
}
