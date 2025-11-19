<?php

namespace Growfund\App\Events;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\ActivityType;
use Growfund\DTO\Activity\ActivityDTO;

class CampaignPostUpdateEvent
{
    /** @var int */
    public $campaign_id;

    /** @var int */
    public $post_update_id;

    public function __construct(int $campaign_id, int $post_update_id)
    {
        $this->campaign_id = $campaign_id;
        $this->post_update_id = $post_update_id;
    }

    /**
     * Get the activity DTO.
     * 
     * @return ActivityDTO
     */
    public function get_activity_dto()
    {
        $dto = new ActivityDTO();
        $dto->type = ActivityType::CAMPAIGN_POST_UPDATE;
        $dto->campaign_id = $this->campaign_id;
        $dto->data = wp_json_encode([
            'post_update_id' => (string) $this->post_update_id
        ]);

        return $dto;
    }
}
