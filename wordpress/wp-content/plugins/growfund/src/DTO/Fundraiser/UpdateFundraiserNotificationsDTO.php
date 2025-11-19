<?php

namespace Growfund\DTO\Fundraiser;

defined( 'ABSPATH' ) || exit;

use Growfund\DTO\DTO;
use Growfund\Sanitizer;

class UpdateFundraiserNotificationsDTO extends DTO
{
    /** @var boolean */
    public $is_enabled_fundraiser_email_campaign_declined;

    /** @var boolean */
    public $is_enabled_fundraiser_email_campaign_approved;

    /** @var boolean */
    public $is_enabled_fundraiser_email_campaign_funded;

    /** @var boolean */
    public $is_enabled_fundraiser_email_pledge_cancelled;

    /** @var boolean */
    public $is_enabled_fundraiser_email_new_pledge;

    /** @var boolean */
    public $is_enabled_fundraiser_email_new_donation;

    /** @var boolean */
    public $is_enabled_fundraiser_email_reward_delivered;

    /** @var boolean */
    public $is_enabled_fundraiser_email_campaign_post_update;

    /**
     * @return array<string, string>
     */
    public static function validation_rules()
    {
        return [
            'is_enabled_fundraiser_email_campaign_declined' => 'required|boolean',
            'is_enabled_fundraiser_email_campaign_approved' => 'required|boolean',
            'is_enabled_fundraiser_email_campaign_funded'   => 'required|boolean',
            'is_enabled_fundraiser_email_pledge_cancelled' => 'required|boolean',
            'is_enabled_fundraiser_email_new_pledge'  => 'required|boolean',
            'is_enabled_fundraiser_email_new_donation'  => 'required|boolean',
            'is_enabled_fundraiser_email_reward_delivered'  => 'required|boolean',
            'is_enabled_fundraiser_email_campaign_post_update'   => 'required|boolean',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function sanitization_rules()
    {
        return [
            'is_enabled_fundraiser_email_campaign_declined' => Sanitizer::BOOL,
            'is_enabled_fundraiser_email_campaign_approved' => Sanitizer::BOOL,
            'is_enabled_fundraiser_email_campaign_funded'   => Sanitizer::BOOL,
            'is_enabled_fundraiser_email_pledge_cancelled' => Sanitizer::BOOL,
            'is_enabled_fundraiser_email_new_pledge'  => Sanitizer::BOOL,
            'is_enabled_fundraiser_email_new_donation'  => Sanitizer::BOOL,
            'is_enabled_fundraiser_email_reward_delivered'  => Sanitizer::BOOL,
            'is_enabled_fundraiser_email_campaign_post_update'   => Sanitizer::BOOL,
        ];
    }
}
