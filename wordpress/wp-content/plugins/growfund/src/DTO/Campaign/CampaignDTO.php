<?php

namespace Growfund\DTO\Campaign;

defined( 'ABSPATH' ) || exit;

use Growfund\CastAttributes\BooleanAttribute;
use Growfund\CastAttributes\DateTimeAttribute;
use Growfund\CastAttributes\MoneyAttribute;
use Growfund\CastAttributes\StringAttribute;
use Growfund\DTO\DTO;
use Growfund\DTO\User\UserInfoDTO;
use Growfund\Supports\Arr;
use Growfund\Supports\CampaignGoal;
use Growfund\Supports\Money;

/**
 * Data Transfer Object for Campaign
 *
 * @since 1.0.0
 */
class CampaignDTO extends DTO
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

    /** @var array|null */
    public $images;

    /** @var array|null */
    public $video;

    /** @var bool|null */
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

    /** @var int[]|null */
    public $collaborators;

    /** @var bool|null */
    public $show_collaborator_list;

    /** @var string|null */
    public $status;

    /** @var string|null */
    public $risk;

    /** @var bool|null */
    public $has_goal;

    /** @var bool|null */
    public $is_half_goal_achieved_already;

    /** @var string|null */
    public $goal_type;

    /** @var float|int|null */
    public $goal_amount;

    /** @var string|null */
    public $reaching_action;

    /** @var string|null */
    public $confirmation_title;

    /** @var string|null */
    public $confirmation_description;

    /** @var bool|null */
    public $provide_confirmation_pdf_receipt;

    /** @var int */
    public $number_of_contributors;

    /** @var int  */
    public $number_of_contributions;

    /** @var string|null */
    public $appreciation_type;

    /** @var array|null */
    public $giving_thanks;

    /** @var array|null */
    public $rewards;

    /** @var bool */
    public $allow_local_pickup = false;

    /** @var bool */
    public $is_ready_for_pickup = false;

    /** @var bool|null */
    public $allow_pledge_without_reward;

    /** @var float|null */
    public $min_pledge_amount;

    /** @var float|null */
    public $max_pledge_amount;

    /** @var int */
    public $uncharged_pledge_count;

    /** @var bool */
    public $is_backed;

    /** @var bool */
    public $is_paused;

    /** @var bool */
    public $is_hidden;

    /** @var array */
    public $tribute_options;

    /** @var string|null */
    public $last_decline_reason;

    /** @var UserInfoDTO|null */
    public $author;

    /** @var UserInfoDTO|null */
    public $fundraiser;

    /** @var bool*/
    public $is_interactive;

    /** @var bool */
    public $is_launched;

    /** @var bool */
    public $is_ended;

    /** @var float */
    public $fund_raised;

    /** @var int */
    public $number_of_individual_contributions;

    /** @var string|null */
    public $preview_url;

    /** @var bool|null */
    public $allow_custom_donation;

    /** @var string|null */
    public $min_donation_amount;

    /** @var string|null */
    public $max_donation_amount;

    /** @var string */
    public $suggested_option_type;

    /** @var array */
    public $suggested_options;

    /** @var bool */
    public $has_tribute;

    /** @var string|null */
    public $tribute_requirement;

    /** @var string|null */
    public $tribute_title;

    /** @var string|null */
    public $tribute_notification_preference;

    /** @var string|null */
    public $fund_selection_type;

    /** @var string|null */
    public $default_fund;

    /** @var string[]|null */
    public $fund_choices;

    /** @var array|null */
    public $faqs;

    /** @var bool */
    public $is_bookmarked = false;

    protected function get_casts()
    {
        return [
            'start_date' => DateTimeAttribute::class,
            'end_date' => DateTimeAttribute::class,
            'has_goal' => BooleanAttribute::class,
            'is_half_goal_achieved_already' => BooleanAttribute::class,
            'fund_raised' => MoneyAttribute::class,
            'max_pledge_amount' => MoneyAttribute::class,
            'min_pledge_amount' => MoneyAttribute::class,
            'allow_pledge_without_reward' => BooleanAttribute::class,
            'provide_confirmation_pdf_receipt' => BooleanAttribute::class,
            'show_collaborator_list' => BooleanAttribute::class,
            'is_launched' => BooleanAttribute::class,
            'is_ended' => BooleanAttribute::class,
            'is_paused' => BooleanAttribute::class,
            'is_hidden' => BooleanAttribute::class,
            'goal_amount' => function () {
                if (is_null($this->goal_amount)) {
                    return null;
                }

                return CampaignGoal::prepare_goal_for_display(
                    $this->goal_type,
                    $this->goal_amount
                );
            },
            'suggested_options' => function () {
                if (empty($this->suggested_options)) {
                    return [];
                }

                return Arr::make($this->suggested_options)->map(function ($option) {
                    return [
                        'amount' => Money::prepare_for_display($option['amount']),
                        'description' => $option['description'] ?? '',
                        'is_default' => boolval($option['is_default'] ?? false),
                    ];
                })->toArray();
            },
            'min_donation_amount' => MoneyAttribute::class,
            'max_donation_amount' => MoneyAttribute::class,
            'has_tribute' => BooleanAttribute::class,
            'giving_thanks.*.pledge_from' => MoneyAttribute::class,
            'giving_thanks.*.pledge_to' => MoneyAttribute::class,
            'author' => UserInfoDTO::class,
            'fundraiser' => UserInfoDTO::class,
            'collaborators.*' => StringAttribute::class,
        ];
    }
}
