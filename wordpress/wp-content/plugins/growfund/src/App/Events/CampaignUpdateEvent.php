<?php

namespace Growfund\App\Events;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\ActivityType;
use Growfund\Constants\DateTimeFormats;
use Growfund\Constants\Status\CampaignStatus;
use Growfund\DTO\Activity\ActivityDTO;
use Growfund\DTO\Campaign\CampaignDTO;
use Growfund\DTO\Campaign\UpdateCampaignDTO;
use DateTime;

class CampaignUpdateEvent
{
    /** @var CampaignDTO */
    public $campaign;

    /** @var UpdateCampaignDTO  */
    public $dto;

    /**
     * Initialize the event with campaign and dto.
     * @param CampaignDTO $campaign
     * @param UpdateCampaignDTO $dto
     */
    public function __construct(CampaignDTO $campaign, UpdateCampaignDTO $dto)
    {
        $this->campaign = $campaign;
        $this->dto = $dto;
    }

    /**
     * @param DateTime|null $old_end_date
     * @param DateTime|null $new_end_date
     * @return bool
     */
    public function is_set_new_deadline($old_end_date, $new_end_date)
    {
        return empty($old_end_date) && !empty($new_end_date);
    }

    /**
     * @param DateTime|null $old_end_date
     * @param DateTime|null $new_end_date
     * @return bool
     */
    public function is_removed_deadline($old_end_date, $new_end_date)
    {
        return !empty($old_end_date) && empty($new_end_date);
    }

    /**
     * @param DateTime|null $old_end_date
     * @param DateTime|null $new_end_date
     * @return bool
     */
    public function is_extended_deadline($old_end_date, $new_end_date)
    {
        if (empty($old_end_date) || empty($new_end_date)) {
            return false;
        }

        $date_diff = $old_end_date->diff($new_end_date)->days;

        return $date_diff > 0;
    }

    /**
     * @param string $old_status
     * @param string $new_status
     * @return bool
     */
    public function is_published($old_status, $new_status)
    {
        return $old_status !== CampaignStatus::PUBLISHED && $new_status === CampaignStatus::PUBLISHED;
    }

    /**
     * @param string $old_status
     * @param string $new_status
     * @return bool
     */
    public function is_declined($old_status, $new_status)
    {
        return $old_status !== CampaignStatus::DECLINED && $new_status === CampaignStatus::DECLINED;
    }

    /**
     * @param string $old_status
     * @param string $new_status
     * @return bool
     */
    public function is_submitted_for_review($old_status, $new_status)
    {
        return growfund_user()->is_fundraiser() && $new_status === CampaignStatus::PENDING
            && $old_status !== CampaignStatus::PENDING && $old_status !== CampaignStatus::DECLINED;
    }

    /**
     * @param string $old_status
     * @param string $new_status
     * @return bool
     */
    public function is_resubmitted_for_review($old_status, $new_status)
    {
        return $this->is_submitted_for_review($old_status, $new_status) && $old_status === CampaignStatus::DECLINED;
    }

    /**
     * @param string $old_status
     * @param string $new_status
     * @return bool
     */
    public function is_marked_as_completed($old_status, $new_status)
    {
        return $old_status !== CampaignStatus::COMPLETED && $new_status === CampaignStatus::COMPLETED;
    }

    /**
     * Get the activity dto for the campaign update event.
     * @return ActivityDTO|null
     */
    public function get_activity_dto()
    {
        $old_end_date = !empty($this->campaign->end_date)
            ? new DateTime($this->campaign->end_date)
            : null;
        $new_end_date = !empty($this->dto->end_date)
            ? new DateTime($this->dto->end_date)
            : null;

        if ($this->is_set_new_deadline($old_end_date, $new_end_date)) {
            return ActivityDTO::from_array([
                'type' => ActivityType::CAMPAIGN_SET_DEADLINE,
                'campaign_id' => $this->campaign->id,
                'data' => wp_json_encode([
                    'new_end_date' => $new_end_date->format(DateTimeFormats::DB_DATE)
                ])
            ]);
        }

        if ($this->is_removed_deadline($old_end_date, $new_end_date)) {
            return ActivityDTO::from_array([
                'type' => ActivityType::CAMPAIGN_REMOVED_DEADLINE,
                'campaign_id' => $this->campaign->id
            ]);
        }


        if ($this->is_extended_deadline($old_end_date, $new_end_date)) {
            $date_diff = $old_end_date->diff($new_end_date)->days;
            return ActivityDTO::from_array([
                'type' => ActivityType::CAMPAIGN_EXTENDED_DEADLINE,
                'campaign_id' => $this->campaign->id,
                'data' => wp_json_encode([
                    'old_end_date' => $old_end_date->format(DateTimeFormats::DB_DATE),
                    'new_end_date' => $new_end_date->format(DateTimeFormats::DB_DATE),
                    'no_of_extended_days' => $date_diff
                ])
            ]);
        }

        return null;
    }
}
