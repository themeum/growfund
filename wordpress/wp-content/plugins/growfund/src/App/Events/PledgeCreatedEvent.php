<?php

namespace Growfund\App\Events;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\ActivityType;
use Growfund\DTO\Activity\ActivityDTO;
use Growfund\DTO\Campaign\CampaignDTO;
use Growfund\DTO\Pledge\CreatePledgeDTO;

class PledgeCreatedEvent
{
    /** @var CreatePledgeDTO */
    public $create_pledge_dto;

    /** @var int */
    public $pledge_id;

    /** @var CampaignDTO */
    public $campaign;

    /**
     * Initialize the event with campaign id and pledge amount.
     * @param int $campaign_id
     * @param int $pledge_id
     * @param int $pledge_amount
     */
    public function __construct($pledge_id, CreatePledgeDTO $create_pledge_dto, CampaignDTO $campaign)
    {
        $this->create_pledge_dto = $create_pledge_dto;
        $this->pledge_id = $pledge_id;
        $this->campaign = $campaign;
    }

    public function get_activity_dto()
    {
        $dto = new ActivityDTO();
        $dto->type = ActivityType::PLEDGE_CREATION;
        $dto->campaign_id = $this->create_pledge_dto->campaign_id;
        $dto->pledge_id = $this->pledge_id;
        $dto->user_id = $this->create_pledge_dto->user_id;
        $dto->data = wp_json_encode([
            'pledge_amount' => ($this->create_pledge_dto->amount +
                $this->create_pledge_dto->bonus_support_amount +
                $this->create_pledge_dto->shipping_cost)
        ]);

        return $dto;
    }
}
