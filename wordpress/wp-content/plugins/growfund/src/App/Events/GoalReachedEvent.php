<?php

namespace Growfund\App\Events;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\ActivityType;
use Growfund\DTO\Activity\ActivityDTO;
use Growfund\DTO\Campaign\CampaignDTO;

class GoalReachedEvent
{
    /** @var CampaignDTO */
    public $campaign;

    public function __construct(CampaignDTO $campaign)
    {
        $this->campaign = $campaign;
    }

    public function get_activity_dto()
    {
        $dto = new ActivityDTO();
        $dto->type = ActivityType::CAMPAIGN_GOAL_REACHED;
        $dto->campaign_id = $this->campaign->id;

        return $dto;
    }
}
