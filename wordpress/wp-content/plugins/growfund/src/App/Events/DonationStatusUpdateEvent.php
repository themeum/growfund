<?php

namespace Growfund\App\Events;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\ActivityType;
use Growfund\Constants\Status\DonationStatus;
use Growfund\DTO\Activity\ActivityDTO;
use Growfund\DTO\Donation\DonationDTO;

class DonationStatusUpdateEvent
{
    /** @var DonationDTO */
    public $donation;

    /** @var string */
    public $status;

    /**
     * Initialize donation update event
     * @param DonationDTO $donation
     * @param string $status
     */
    public function __construct(DonationDTO $donation, string $status)
    {
        $this->donation = $donation;
        $this->status = $status;
    }

    public function is_cancelled($old_status, $new_status)
    {
        return $old_status !== DonationStatus::CANCELLED && $new_status === DonationStatus::CANCELLED;
    }

    public function is_failed($old_status, $new_status)
    {
        return $old_status !== DonationStatus::FAILED && $new_status === DonationStatus::FAILED;
    }

    public function is_completed($old_status, $new_status)
    {
        return $old_status !== DonationStatus::COMPLETED && $new_status === DonationStatus::COMPLETED;
    }

    public function get_activity_dto()
    {
        $campaign_id = $this->donation->campaign->id;
        $donation_id = $this->donation->id;
        $user_id = $this->donation->donor->id;
        $old_status = $this->donation->status;
        $new_status = $this->status;

        if ($this->is_cancelled($old_status, $new_status)) {
            return ActivityDTO::from_array([
                'type' => ActivityType::DONATION_CANCELLED,
                'campaign_id' => $campaign_id,
                'donation_id' => $donation_id,
                'user_id' => $user_id
            ]);
        }

        if ($this->is_failed($old_status, $new_status)) {
            return ActivityDTO::from_array([
                'type' => ActivityType::DONATION_FAILED,
                'campaign_id' => $campaign_id,
                'donation_id' => $donation_id,
                'user_id' => $user_id
            ]);
        }

        if ($this->is_completed($old_status, $new_status)) {
            return ActivityDTO::from_array([
                'type' => ActivityType::DONATION_COMPLETED,
                'campaign_id' => $campaign_id,
                'donation_id' => $donation_id,
                'user_id' => $user_id,
            ]);
        }
    }
}
