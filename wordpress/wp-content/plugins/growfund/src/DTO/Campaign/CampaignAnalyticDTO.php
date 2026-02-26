<?php

namespace Growfund\DTO\Campaign;

defined( 'ABSPATH' ) || exit;

use Growfund\CastAttributes\BooleanAttribute;
use Growfund\CastAttributes\MoneyAttribute;
use Growfund\DTO\DTO;
use Growfund\Supports\CampaignGoal;

/**
 * Data Transfer Object for Campaign
 *
 * @since 1.0.0
 */
class CampaignAnalyticDTO extends DTO
{
    /**
     * Fields that are considered not part of "meta" data.
     *
     * @var array
     */
    protected static $base_fields = ['id', 'title', 'description', 'slug', 'category', 'sub_category', 'tags'];

    /** @var int|string|null */
    public $id;

    /** @var string|null */
    public $title;

    /** @var string|null */
    public $status;

    /** @var bool|null */
    public $has_goal;

    /** @var string|null */
    public $goal_type;

    /** @var float|int|null */
    public $goal_amount;

    /** @var int */
    public $number_of_contributors;

    /** @var int  */
    public $number_of_contributions;

    /** @var int */
    public $fund_raised;

    /** @var array|null */
    public $images;

    protected function get_casts()
    {
        return [
            'has_goal' => BooleanAttribute::class,
            'fund_raised' => MoneyAttribute::class,
            'goal_amount' => function () {
                return CampaignGoal::prepare_goal_for_display($this->goal_type, $this->goal_amount);
            }
        ];
    }
}
