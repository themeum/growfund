<?php

namespace Growfund\DTO\Activity;

defined( 'ABSPATH' ) || exit;

use Growfund\CastAttributes\MoneyAttribute;
use Growfund\DTO\DTO;

class ActivityResponseDTO extends DTO
{
    /** @var string|null */
    public $id;

    /** @var \Growfund\Constants\ActivityType */
    public $type;

    /** @var string */
    public $campaign_id;

    public $campaign_title;

    public $campaign_images;

    /** @var string */
    public $pledge_id;

    /** @var string */
    public $donation_id;

    /** 
     * @var array
     * - comment string
     * - post_update_id string
     * - old_end_date string
     * - new_end_date string
     * - no_of_extended_days int
     * - donation_amount int
     * - pledge_amount int
     */
    public $data;

    /** @var string */
    public $user_id;

    /** @var string */
    public $created_by;

    /** @var string */
    public $created_by_name;

    /** @var string */
    public $created_by_image;

    /** @var string */
    public $created_at;

    protected function get_casts()
    {
        return [
            'data.pledge_amount' => MoneyAttribute::class,
            'data.donation_amount' => MoneyAttribute::class,
        ];
    }
}
