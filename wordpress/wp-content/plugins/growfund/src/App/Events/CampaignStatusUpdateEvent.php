<?php

namespace Growfund\App\Events;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\ActivityType;
use Growfund\DTO\Activity\ActivityDTO;
use Growfund\DTO\Campaign\CampaignDTO;

class CampaignStatusUpdateEvent extends CampaignUpdateEvent
{
    public $status;
    public $campaign;

    public function __construct(CampaignDTO $campaign, string $status)
    {
        $this->status = $status;
        $this->campaign = $campaign;
    }

    public function get_activity_dto()
    {
        $campaign_id = $this->campaign->id;
        $old_status = $this->campaign->status;
        $new_status = $this->status;

        if ($this->is_submitted_for_review($old_status, $new_status)) {
            return ActivityDTO::from_array([
                'type' => ActivityType::CAMPAIGN_SUBMITTED_FOR_REVIEW,
                'campaign_id' => $campaign_id,
            ]);
        }

        if ($this->is_resubmitted_for_review($old_status, $new_status)) {
            return ActivityDTO::from_array([
                'type' => ActivityType::CAMPAIGN_RE_SUBMITTED_FOR_REVIEW,
                'campaign_id' => $campaign_id,
            ]);
        }

        if ($this->is_declined($old_status, $new_status)) {
            return ActivityDTO::from_array([
                'type' => ActivityType::CAMPAIGN_DECLINED,
                'campaign_id' => $campaign_id,
            ]);
        }

        if ($this->is_published($old_status, $new_status)) {
            return ActivityDTO::from_array([
                'type' => ActivityType::CAMPAIGN_APPROVED_AND_PUBLISHED,
                'campaign_id' => $campaign_id,
            ]);
        }

        if ($this->is_marked_as_completed($old_status, $new_status)) {
            return ActivityDTO::from_array([
                'type' => ActivityType::CAMPAIGN_MARKED_AS_COMPLETED,
                'campaign_id' => $campaign_id,
            ]);
        }

        return null;
    }
}
