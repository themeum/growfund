<?php

namespace Growfund\DTO\Donor;

defined( 'ABSPATH' ) || exit;

use Growfund\DTO\DTO;
use Growfund\Sanitizer;

class UpdateDonorNotificationsDTO extends DTO
{
    /** @var boolean */
    public $is_enabled_donor_email_campaign_post_update;

    /** @var boolean */
    public $is_enabled_donor_email_campaign_half_milestone_achieved;

    public static function validation_rules()
    {
        return [
            'is_enabled_donor_email_campaign_post_update'             => 'required|boolean',
            'is_enabled_donor_email_campaign_half_milestone_achieved'   => 'required|boolean',
        ];
    }

    public static function sanitization_rules()
    {
        return [
            'is_enabled_donor_email_campaign_post_update'             => Sanitizer::BOOL,
            'is_enabled_donor_email_campaign_half_milestone_achieved'   => Sanitizer::BOOL,
        ];
    }
}
