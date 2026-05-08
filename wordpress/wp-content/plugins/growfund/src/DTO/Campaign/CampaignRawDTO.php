<?php

namespace Growfund\DTO\Campaign;

defined( 'ABSPATH' ) || exit;

use Growfund\CastAttributes\MoneyAttribute;
use Growfund\DTO\DTO;
use Growfund\Supports\CampaignGoal;

/**
 * Data Transfer Object for Campaign
 *
 * @since 1.0.0
 */
class CampaignRawDTO extends DTO
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
    public $slug;

    /** @var string|null */
    public $description;

    /** @var string|null */
    public $story;

    /** @var string[]|null */
    public $images;

    /** @var array{id:string;poster:string}|null */
    public $video;

    /** @var bool */
    public $is_featured;

    /** @var int|null */
    public $category;

    /** @var int|null */
    public $sub_category;

    /** @var string|null */
    public $start_date;

    /** @var string|null */
    public $end_date;

    /** @var string|null */
    public $location;

    /** @var array|null */
    public $tags;

    /** @var array|null */
    public $collaborators;

    /** @var bool|null */
    public $show_collaborator_list;

    /** @var string|null */
    public $status;

    /** @var string|null */
    public $risk;

    /** @var bool|null */
    public $has_goal;

    /** @var string|null */
    public $goal_type;

    /** @var string|null */
    public $reaching_action;

    /** @var string|null */
    public $confirmation_title;

    /** @var string|null */
    public $confirmation_description;

    /** @var bool|null */
    public $provide_confirmation_pdf_receipt;

    /** @var float|null */
    public $goal_amount;

    /** @var string|null */
    public $appreciation_type;

    /** @var array|null */
    public $giving_thanks;

    /** @var string[]|null */
    public $rewards;

    /** @var bool|null */
    public $allow_pledge_without_reward;

    /** @var float|null */
    public $min_pledge_amount;

    /** @var float|null */
    public $max_pledge_amount;

    /** @var bool|null */
    public $allow_custom_donation;

    /** @var string|null */
    public $min_donation_amount;

    /** @var string|null */
    public $max_donation_amount;

    /** @var string|null */
    public $suggested_option_type;

    /** @var array|null */
    public $suggested_options;

    /** @var bool|null */
    public $has_tribute;

    /** @var string|null */
    public $tribute_requirement;

    /** @var string|null */
    public $tribute_title;

    /** @var array|null */
    public $tribute_options;

    /** @var string|null */
    public $tribute_notification_preference;

    /** @var string|null */
    public $fund_selection_type;

    /** @var string|null */
    public $default_fund;

    /** @var array|null */
    public $fund_choices;

    /** @var array|null */
    public $faqs;

    /** @var bool */
    public $is_paused;

    /** @var bool */
    public $is_hidden;

    protected function get_casts()
    {
        return [
            'suggested_options.*.amount' => MoneyAttribute::class,
            'min_donation_amount' => MoneyAttribute::class,
            'max_donation_amount' => MoneyAttribute::class,
            'min_pledge_amount' => MoneyAttribute::class,
            'max_pledge_amount' => MoneyAttribute::class,
            'goal_amount' => function () {
                if (is_null($this->goal_amount)) {
                    return null;
                }

                return CampaignGoal::prepare_goal_for_display(
                    $this->goal_type,
                    $this->goal_amount
                );
            },
            'giving_thanks.*.pledge_from' => MoneyAttribute::class,
            'giving_thanks.*.pledge_to' => MoneyAttribute::class,
        ];
    }
}
