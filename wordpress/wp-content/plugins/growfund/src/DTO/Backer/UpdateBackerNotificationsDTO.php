<?php

namespace Growfund\DTO\Backer;

defined( 'ABSPATH' ) || exit;

use Growfund\DTO\DTO;
use Growfund\Sanitizer;

class UpdateBackerNotificationsDTO extends DTO
{
    /** @var boolean */
    public $is_enabled_backer_email_campaign_post_update;

    /** @var boolean */
    public $is_enabled_backer_email_campaign_half_funded;

    /** @var boolean */
    public $is_enabled_backer_email_reward_delivered;

    /**
     * @return array<string, string>
     */
    public static function validation_rules()
    {
        return [
            'is_enabled_backer_email_campaign_post_update' => 'required|boolean',
            'is_enabled_backer_email_campaign_half_funded'  => 'required|boolean',
            'is_enabled_backer_email_reward_delivered'      => 'required|boolean',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function sanitization_rules()
    {
        return [
            'is_enabled_backer_email_campaign_post_update' => Sanitizer::BOOL,
            'is_enabled_backer_email_campaign_half_funded' => Sanitizer::BOOL,
            'is_enabled_backer_email_reward_delivered'     => Sanitizer::BOOL,
        ];
    }
}
