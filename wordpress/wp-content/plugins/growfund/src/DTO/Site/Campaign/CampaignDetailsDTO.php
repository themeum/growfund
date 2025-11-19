<?php

namespace Growfund\DTO\Site\Campaign;

defined( 'ABSPATH' ) || exit;

use Growfund\CastAttributes\MoneyAttribute;
use Growfund\DTO\DTO;
use Growfund\DTO\RewardDTO;
use Growfund\DTO\Site\Campaign\CampaignPost\CampaignPostDTO;
use Growfund\DTO\Site\Campaign\CampaignAuthorDTO;
use Growfund\DTO\Site\Comment\CommentCardItemDTO;
use Growfund\DTO\Site\Campaign\TimelineDatesDTO;
use Growfund\DTO\Site\OrderConfirmationDTO;
use Growfund\Supports\CampaignGoal;

/**
 * DTO for campaign details with author and update information
 */
class CampaignDetailsDTO extends DTO
{
    /** @var string */
    public $id;

    /** @var string */
    public $title;

    /** @var string */
    public $description;

    /** @var array|null */
    public $video;

    /** @var array|null */
    public $images;

    /** @var array */
    public $tags;

    /** @var string */
    public $slug;

    /** @var string|null */
    public $thumbnail;

    /** @var string|null */
    public $start_date;

    /** @var string|null */
    public $end_date;

    /** @var float */
    public $fund_raised = 0.0;

    /** @var bool */
    public $has_goal;

    /** @var string|null */
    public $goal_type;

    /** @var float|null */
    public $goal_amount;

    /** @var int */
    public $number_of_contributors = 0;

    /** @var int */
    public $number_of_contributions = 0;

    /** @var string|null */
    public $location;

    /** @var string|null */
    public $story;

    /** @var string|null */
    public $appreciation_type;

    /** @var bool */
    public $allow_pledge_without_reward = false;

    /** @var float */
    public $min_pledge_amount = 0;

    /** @var float */
    public $max_pledge_amount = 0;

    /** @var RewardDTO[] */
    public $rewards;

    /** @var CampaignPostDTO[] */
    public $campaign_updates;

    /** @var int */
    public $total_campaign_updates_count = 0;

    /** @var array */
    public $related_campaigns;

    /** @var array */
    public $comment_form_data;

    /** @var CommentCardItemDTO[] */
    public $comments;

    /** @var int */
    public $total_comments_count = 0;

    /** @var CampaignAuthorDTO */
    public $author;

    /** @var string */
    public $update_badge;

    /** @var string|null */
    public $update_content_full;

    /** @var array */
    public $update_stats;

    /** @var TimelineDatesDTO */
    public $timeline_dates;

    /** @var array */
    public $campaign_donations = [];

    /** @var int */
    public $campaign_donations_count = 0;

    /** @var string|null */
    public $checkout_url;

    /** @var bool */
    public $can_see_campaign_updates = true;

    /** @var bool */
    public $can_show_donations = false;

    /** @var bool */
    public $is_bookmarked = false;

    /** @var array|null */
    public $faqs;

    /** @var OrderConfirmationDTO|null */
    public $contribution = null;
    /**
     * @var CampaignAuthorDTO[]
     */
    public $collaborators = [];

    /** @var bool */
    public $is_closed = false;

    protected function get_casts()
    {
        return [
            'fund_raised' => MoneyAttribute::class,
            'min_pledge_amount' => MoneyAttribute::class,
            'max_pledge_amount' => MoneyAttribute::class,
            'goal_amount' => function () {
                if (!isset($this->goal_type) || !isset($this->goal_amount)) {
                    return $this->goal_amount;
                }

                return CampaignGoal::prepare_goal_for_display($this->goal_type, $this->goal_amount);
            }
        ];
    }
}
