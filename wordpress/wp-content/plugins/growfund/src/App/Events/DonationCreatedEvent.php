<?php

namespace Growfund\App\Events;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\ActivityType;
use Growfund\DTO\Activity\ActivityDTO;
use Growfund\DTO\Campaign\CampaignDTO;
use Growfund\DTO\Donation\CreateDonationDTO;

class DonationCreatedEvent
{
    /** @var CreateDonationDTO */
    public $donation_create_dto;

    /** @var int */
    public $donation_id;

    /** @var CampaignDTO */
    public $campaign;

    /**
     * Initialize the event with campaign id and donation amount.
     * @param CreateDonationDTO $donation_create_dto
     * @param int $donation_amount
     */
    public function __construct(CreateDonationDTO $donation_create_dto, int $donation_id, CampaignDTO $campaign)
    {
        $this->donation_create_dto = $donation_create_dto;
        $this->donation_id = $donation_id;
        $this->campaign = $campaign;
    }

    public function get_activity_dto()
    {
        $activity = new ActivityDTO();
        $activity->type = ActivityType::DONATION_CREATION;
        $activity->campaign_id = $this->donation_create_dto->campaign_id;
        $activity->donation_id = $this->donation_id;
        $activity->user_id = $this->donation_create_dto->user_id;
        $activity->data = wp_json_encode([
            'donation_amount' => $this->donation_create_dto->amount
        ]);

        return $activity;
    }
}
