<?php

namespace Growfund\DTO\Donation;

defined( 'ABSPATH' ) || exit;

use Growfund\CastAttributes\DateTimeAttribute;
use Growfund\CastAttributes\MoneyAttribute;
use Growfund\DTO\DTO;
use Growfund\Supports\CampaignGoal;

/**
 * Data Transfer Object for a Donation's Campaign List Item in Donations Page
 *
 * @since 1.0.0
 */
class DonationCampaignDTO extends DTO
{
    /** @var int */
    public $id;

    /** @var string */
    public $slug;

    /** @var string */
    public $title;

    /** @var string */
    public $status;

    /** @var float */
    public $fund_raised;

    /** @var string */
    public $goal_type;

    /** @var float */
    public $goal_amount;

    /** @var \Growfund\Supports\MediaAttachment[] */
    public $images;

    /** @var string */
    public $start_date;

    /** @var string */
    public $created_by;

    /** @var string */
    public $author_id;

    /** @var bool */
    public $is_launched;

    protected function get_casts()
    {
        return [
            'start_date' => DateTimeAttribute::class,
            'fund_raised' => MoneyAttribute::class,
            'goal_amount' => function () {
                return CampaignGoal::prepare_goal_for_display($this->goal_type, $this->goal_amount);
            }
        ];
    }
}
