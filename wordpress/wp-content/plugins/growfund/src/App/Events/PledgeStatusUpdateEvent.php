<?php

namespace Growfund\App\Events;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\ActivityType;
use Growfund\Constants\Status\PledgeStatus;
use Growfund\DTO\Activity\ActivityDTO;
use Growfund\DTO\Pledge\PledgeDTO;

class PledgeStatusUpdateEvent
{
    /** @var PledgeDTO */
    public $pledge;

    /** @var string */
    public $status;

    /**
     * Initialize the event with pledge and dto.
     * @param object $pledge
     * @param string $dto
     */
    public function __construct(PledgeDTO $pledge, string $status)
    {
        $this->pledge = $pledge;
        $this->status = $status;
    }

    public function is_cancelled($old_status, $new_status)
    {
        return $old_status !== PledgeStatus::CANCELLED && $new_status === PledgeStatus::CANCELLED;
    }

    public function is_in_progress($old_status, $new_status)
    {
        return $old_status !== PledgeStatus::IN_PROGRESS && $new_status === PledgeStatus::IN_PROGRESS;
    }


    public function is_backed($old_status, $new_status)
    {
        return $old_status !== PledgeStatus::BACKED && $new_status === PledgeStatus::BACKED;
    }

    public function is_failed($old_status, $new_status)
    {
        return $old_status !== PledgeStatus::FAILED && $new_status === PledgeStatus::FAILED;
    }

    public function is_completed($old_status, $new_status)
    {
        return $old_status !== PledgeStatus::COMPLETED && $new_status === PledgeStatus::COMPLETED;
    }

    public function get_activity_dto()
    {
        $campaign_id = $this->pledge->campaign->id;
        $pledge_id = $this->pledge->id;
        $user_id = $this->pledge->backer->id;
        $old_status = $this->pledge->status;
        $new_status = $this->status;

        if ($this->is_cancelled($old_status, $new_status)) {
            return ActivityDTO::from_array([
                'type' => ActivityType::PLEDGE_CANCELLED,
                'campaign_id' => $campaign_id,
                'pledge_id' => $pledge_id,
                'user_id' => $user_id,
            ]);
        }

        if ($this->is_backed($old_status, $new_status)) {
            return ActivityDTO::from_array([
                'type' => ActivityType::PLEDGE_BACKED,
                'campaign_id' => $campaign_id,
                'pledge_id' => $pledge_id,
                'user_id' => $user_id,
            ]);
        }

        if ($this->is_failed($old_status, $new_status)) {
            return ActivityDTO::from_array([
                'type' => ActivityType::PLEDGE_FAILED_TO_BACK,
                'campaign_id' => $campaign_id,
                'pledge_id' => $pledge_id,
                'user_id' => $user_id,
            ]);
        }

        if ($this->is_completed($old_status, $new_status)) {
            return ActivityDTO::from_array([
                'type' => ActivityType::PLEDGE_COMPLETED,
                'campaign_id' => $campaign_id,
                'pledge_id' => $pledge_id,
                'user_id' => $user_id,
            ]);
        }
    }
}
