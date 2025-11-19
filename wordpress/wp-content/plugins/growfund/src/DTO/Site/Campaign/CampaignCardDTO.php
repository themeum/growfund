<?php

namespace Growfund\DTO\Site\Campaign;

defined( 'ABSPATH' ) || exit;

use Growfund\CastAttributes\MoneyAttribute;
use Growfund\DTO\DTO;
use Growfund\Supports\CampaignGoal;

/**
 * DTO for campaign card data
 */
class CampaignCardDTO extends DTO
{
    /** @var string */
    public $id;

    /** @var string */
    public $title;

    /** @var string */
    public $description;

    /** @var string */
    public $url;

    /** @var string|null */
    public $thumbnail;

    /** @var string|null (format: Y-m-d or Y-m-d H:i:s) */
    public $start_date;

    /** @var string|null (format: Y-m-d or Y-m-d H:i:s) */
    public $end_date;

    /** @var string|null */
    public $author_name;

    /** @var string|null */
    public $author_image;

    /** @var float|int */
    public $fund_raised = 0;

    /** @var bool */
    public $has_goal;

    /** @var float|int */
    public $goal_amount = 0;

    /** @var string|null */
    public $goal_type;

    /** @var int|null */
    public $category_id;

    /** @var int|null */
    public $subcategory_id;

    /** @var bool */
    public $is_featured = false;

    /** @var bool */
    public $is_bookmarked = false;

    /** @var int */
    public $number_of_contributors;

    /** @var int  */
    public $number_of_contributions;

    protected function get_casts()
    {
        return [
            'fund_raised' => MoneyAttribute::class,
            'goal_amount' => function () {
                if (!isset($this->goal_type) || !isset($this->goal_amount)) {
                    return $this->goal_amount;
                }

                return CampaignGoal::prepare_goal_for_display($this->goal_type, $this->goal_amount);
            }
        ];
    }
}
