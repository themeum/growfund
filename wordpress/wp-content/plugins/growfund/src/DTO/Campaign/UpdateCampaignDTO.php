<?php

namespace Growfund\DTO\Campaign;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\AppreciationType;
use Growfund\Constants\Campaign\FundSelectionType;
use Growfund\Constants\Campaign\GoalType;
use Growfund\Constants\Campaign\ReachingAction;
use Growfund\Constants\Campaign\SuggestedOptionType;
use Growfund\Constants\Campaign\TributeNotificationPreference;
use Growfund\Constants\HookNames;
use Growfund\Constants\Status\CampaignStatus;
use Growfund\Core\AppSettings;
use Growfund\DTO\DTO;
use Growfund\PostTypes\Campaign;
use Growfund\Sanitizer;
use Growfund\Supports\CampaignGoal;

/**
 * Data Transfer Object for Campaign
 *
 * @since 1.0.0
 */
class UpdateCampaignDTO extends DTO
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

    /** @var string|null */
    public $created_by;

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

    public static function validation_rules()
    {
        return apply_filters(
            HookNames::GROWFUND_CAMPAIGN_UPDATE_VALIDATION_RULES_FILTER,
            array_merge([
                'id' => 'required|post_exists:post_type=' . Campaign::NAME,
                'title' => 'required|string',
                'slug' => 'string',
                'description' => 'required_if:status,published|string',
                'story' => 'string',
                'images' => 'array',
                'video' => 'array',
                'is_featured' => 'boolean',
                'category' => 'required_if:status,published|integer',
                'sub_category' => 'integer|min:1',
                'end_date' => 'string|after_date:start_date',
                'location' => 'required_if:status,published|string',
                'tags' => 'array',
                'tags.*' => 'integer',
                'collaborators' => 'array',
                'collaborators.*' => 'integer',
                'show_collaborator_list' => 'boolean',
                'status' => 'string|in:' . implode(',', CampaignStatus::get_constant_values()),
                'risk' => 'string',
                'has_goal' => 'boolean',
                'goal_type' => [
                    function ($value, $key, $data) {
                        if ($data['status'] === CampaignStatus::PUBLISHED && $data['has_goal'] === true && empty($value)) {
                            /* translators: %s: field name */
                            return sprintf(__('The %s field is required.', 'growfund'), $key);
                        }

                        return true;
                    },
                    'string',
                    'in:' . implode(',', GoalType::get_constant_values())
                ],
                'goal_amount' => [
                    function ($value, $key, $data) {
                        if ($data['status'] === CampaignStatus::PUBLISHED && $data['has_goal'] === true && empty($value)) {
                            /* translators: %s: field name */
                            return sprintf(__('The %s field is required.', 'growfund'), $key);
                        }

                        return true;
                    },
                    'number',
                    'min:1',
                ],
                'reaching_action' => [
                    function ($value, $key, $data) {
                        if ($data['status'] === CampaignStatus::PUBLISHED && $data['has_goal'] === true && empty($value)) {
                            /* translators: %s: field name */
                            return sprintf(__('The %s field is required.', 'growfund'), $key);
                        }

                        if (!empty($value) && $value !== ReachingAction::CLOSE) {
                            /* translators: 1: field name 2: reaching action */
                            return sprintf(__('The %1$s field must be %2$s.', 'growfund'), $key, ReachingAction::CLOSE);
                        }

                        return true;
                    },
                ],
                'confirmation_title' => 'string',
                'confirmation_description' => 'string',
                'provide_confirmation_pdf_receipt' => 'boolean',
                'faqs' => 'prohibited',
            ], growfund_app()->is_donation_mode() ? array_merge(
                    [
                        'suggested_option_type' => 'in:' . SuggestedOptionType::AMOUNT_ONLY,
                        'suggested_options' => 'required_if:status,published|array',
                        'suggested_options.*.amount' => 'required|number|min:1',
                        'suggested_options.*.is_default' => 'boolean',
                        'allow_custom_donation' => 'prohibited',
                        'min_donation_amount' => [
                            function ($value, $key, $data) {
                                if ($data['status'] === CampaignStatus::PUBLISHED && $data['allow_custom_donation'] === true && empty($value)) {
                                    /* translators: %s: field name */
                                    return sprintf(__('The %s field is required.', 'growfund'), $key);
                                }

                                return true;
                            },
                            'number',
                            'min:1',
                        ],
                        'max_donation_amount' => [
                            function ($value, $key, $data) {
                                if ($data['status'] === CampaignStatus::PUBLISHED && $data['allow_custom_donation'] === true && empty($value)) {
                                    /* translators: %s: field name */
                                    return sprintf(__('The %s field is required.', 'growfund'), $key);
                                }

                                return true;
                            },
                            'number',
                            'min:1',
                            'gt:min_donation_amount',
                        ],
                        'has_tribute' => 'boolean',
                        'tribute_requirement' => [
                            function ($value, $key, $data) {
                                if ($data['status'] === CampaignStatus::PUBLISHED && $data['has_tribute'] === true && empty($value)) {
                                    /* translators: %s: field name */
                                    return sprintf(__('The %s field is required.', 'growfund'), $key);
                                }

                                return true;
                            },
                            'in:optional,required',
                        ],
                        'tribute_title' => [
                            function ($value, $key, $data) {
                                if ($data['status'] === CampaignStatus::PUBLISHED && $data['has_tribute'] === true && empty($value)) {
                                    /* translators: %s: field name */
                                    return sprintf(__('The %s field is required.', 'growfund'), $key);
                                }

                                return true;
                            },
                            'string',
                        ],
                        'tribute_options' => [
                            function ($value, $key, $data) {
                                if ($data['status'] === CampaignStatus::PUBLISHED && $data['has_tribute'] === true && empty($value)) {
                                    /* translators: %s: field name */
                                    return sprintf(__('The %s field is required.', 'growfund'), $key);
                                }

                                return true;
                            },
                            'array',
                        ],
                        'tribute_options.*.message' => [
                            function ($value, $key, $data) {
                                if ($data['status'] === CampaignStatus::PUBLISHED && $data['has_tribute'] === true && empty($value)) {
                                    /* translators: %s: field name */
                                    return sprintf(__('The %s field is required.', 'growfund'), $key);
                                }

                                return true;
                            },
                            'string',
                        ],
                        'tribute_options.*.is_default' => [
                            function ($value, $key, $data) {
                                if ($data['status'] === CampaignStatus::PUBLISHED && $data['has_tribute'] === true && !isset($value)) {
                                    /* translators: %s: field name */
                                    return sprintf(__('The %s field is required.', 'growfund'), $key);
                                }

                                return true;
                            },
                            'boolean',
                        ],
                        'tribute_notification_preference' => [
                            function ($value, $key, $data) {
                                if ($data['status'] === CampaignStatus::PUBLISHED && $data['has_tribute'] === true && empty($value)) {
                                    /* translators: %s: field name */
                                    return sprintf(__('The %s field is required.', 'growfund'), $key);
                                }

                                return true;
                            },
                            'string',
                            'in:' . implode(',', TributeNotificationPreference::get_constant_values())
                        ],
                    ],
                    growfund_settings(AppSettings::CAMPAIGNS)->allow_fund() ? [
                        'fund_selection_type' => 'string|in:' . implode(',', FundSelectionType::get_constant_values()),
                        'default_fund' => [
                            function ($value, $key, $data) {
                                if ($data['status'] === CampaignStatus::PUBLISHED && $data['fund_selection_type'] === FundSelectionType::FIXED && empty($value)) {
                                    /* translators: %s: field name */
                                    return sprintf(__('The %s field is required.', 'growfund'), $key);
                                }

                                return true;
                            },
                            'string',
                        ],
                        'fund_choices' => [
                            function ($value, $key, $data) {
                                if ($data['status'] === CampaignStatus::PUBLISHED && $data['fund_selection_type'] === FundSelectionType::DONOR_DECIDE && empty($value)) {
                                    /* translators: %s: field name */
                                    return sprintf(__('The %s field is required.', 'growfund'), $key);
                                }

                                return true;
                            },
                            'array',
                        ],
                        'fund_choices.*' => 'integer',
                    ] : []
                ) : [
                    'appreciation_type' => 'required_if:status,published|string|in:' . implode(',', AppreciationType::get_constant_values()),
                    'giving_thanks' => [
                        function ($value, $key, $data) {
                            if ($data['status'] === CampaignStatus::PUBLISHED && $data['appreciation_type'] === AppreciationType::GIVING_THANKS && empty($value)) {
                                /* translators: %s: field name */
                                return sprintf(__('The %s field is required.', 'growfund'), $key);
                            }

                            return true;
                        },
                        'array'
                    ],
                    'giving_thanks.*.pledge_from' => 'required|number|min:1',
                    'giving_thanks.*.pledge_to' => [
                        'required',
                        'number',
                        'min:1',
                        function ($value, $key, $data) {
                            $parse = explode('.', $key);
                            $index = $parse[1];

                            if ($data['giving_thanks'][$index]['pledge_from'] > $value) {
                                /* translators: 1: field name, 2: field index */
                                return sprintf(__('The %1$s field must be greater than giving_thanks.%2$s.pledge_from', 'growfund'), $key, $index);
                            }
                            return true;
                        }
                    ],
                    'giving_thanks.*.appreciation_message' => 'required|string',
                    'rewards' => [
                        function ($value, $key, $data) {
                            if ($data['status'] === CampaignStatus::PUBLISHED && $data['appreciation_type'] === AppreciationType::GOODIES && empty($value)) {
                                /* translators: %s: field name */
                                return sprintf(__('The %s field is required.', 'growfund'), $key);
                            }

                            return true;
                        },
                        'array',
                        'max:3',
                    ],
                    'rewards.*' => 'required|integer',
                    'allow_pledge_without_reward' => 'prohibited',
                    'min_pledge_amount' => [
                        function ($value, $key, $data) {
                            if ($data['status'] === CampaignStatus::PUBLISHED && $data['allow_pledge_without_reward'] === true && empty($value)) {
                                /* translators: %s: field name */
                                return sprintf(__('The %s field is required.', 'growfund'), $key);
                            }

                            return true;
                        },
                        'number',
                    ],
                    'max_pledge_amount' => [
                        function ($value, $key, $data) {
                            if ($data['status'] === CampaignStatus::PUBLISHED && $data['allow_pledge_without_reward'] === true && empty($value)) {
                                /* translators: %s: field name */
                                return sprintf(__('The %s field is required.', 'growfund'), $key);
                            }

                            return true;
                        },
                        'number',
                        'gt:min_pledge_amount',
                    ],
                ])
        );
    }

    public static function sanitization_rules()
    {
        return array_merge([
            'id' => Sanitizer::INT,
            'title' => Sanitizer::TEXT,
            'slug' => Sanitizer::TEXT,
            'description' => Sanitizer::TEXTAREA,
            'story' => Sanitizer::RICH_TEXT,
            'images' => Sanitizer::ARRAY ,
            'images.*' => Sanitizer::INT,
            'video' => Sanitizer::ARRAY ,
            'is_featured' => Sanitizer::BOOL,
            'category' => Sanitizer::INT,
            'sub_category' => Sanitizer::INT,
            'start_date' => Sanitizer::DATETIME,
            'end_date' => Sanitizer::DATETIME,
            'location' => Sanitizer::TEXT,
            'tags' => Sanitizer::ARRAY ,
            'tags.*' => Sanitizer::INT,
            'collaborators' => Sanitizer::ARRAY ,
            'collaborators.*' => Sanitizer::INT,
            'show_collaborator_list' => Sanitizer::BOOL,
            'status' => Sanitizer::TEXT,
            'risk' => Sanitizer::TEXTAREA,
            'has_goal' => Sanitizer::BOOL,
            'goal_type' => Sanitizer::TEXT,
            'goal_amount' => function ($goal_amount, $data) {
                return CampaignGoal::prepare_goal_for_storage($data['goal_type'] ?? null, $goal_amount ?? 0);
            },
            'reaching_action' => Sanitizer::TEXT,
            'confirmation_title' => Sanitizer::TEXT,
            'confirmation_description' => Sanitizer::TEXTAREA,
            'provide_confirmation_pdf_receipt' => Sanitizer::BOOL,
            'faqs' => Sanitizer::ARRAY ,
            'faqs.*.question' => Sanitizer::TEXT,
            'faqs.*.answer' => Sanitizer::TEXTAREA,
        ], growfund_app()->is_donation_mode() ? array_merge(
                [
                    'suggested_option_type' => Sanitizer::TEXT,
                    'suggested_options' => Sanitizer::ARRAY ,
                    'suggested_options.*.amount' => Sanitizer::MONEY,
                    'suggested_options.*.description' => Sanitizer::TEXT,
                    'suggested_options.*.is_default' => Sanitizer::BOOL,
                    'allow_custom_donation' => Sanitizer::BOOL,
                    'min_donation_amount' => Sanitizer::MONEY,
                    'max_donation_amount' => Sanitizer::MONEY,
                    'has_tribute' => Sanitizer::BOOL,
                    'tribute_requirement' => Sanitizer::TEXT,
                    'tribute_title' => Sanitizer::TEXT,
                    'tribute_options' => Sanitizer::ARRAY ,
                    'tribute_options.*.message' => Sanitizer::TEXT,
                    'tribute_options.*.is_default' => Sanitizer::BOOL,
                    'tribute_notification_preference' => Sanitizer::TEXT,
                ],
                growfund_settings(AppSettings::CAMPAIGNS)->allow_fund() ? [
                    'fund_selection_type' => Sanitizer::TEXT,
                    'default_fund' => Sanitizer::TEXT,
                    'fund_choices' => Sanitizer::ARRAY,
                    'fund_choices.*' => Sanitizer::INT,
                ] : []
            ) : [
                'appreciation_type' => Sanitizer::TEXT,
                'giving_thanks' => Sanitizer::ARRAY ,
                'giving_thanks.*.pledge_from' => Sanitizer::FLOAT,
                'giving_thanks.*.pledge_to' => Sanitizer::FLOAT,
                'giving_thanks.*.appreciation_message' => Sanitizer::TEXTAREA,
                'rewards' => Sanitizer::ARRAY ,
                'rewards.*' => Sanitizer::INT,
                'allow_pledge_without_reward' => Sanitizer::BOOL,
                'min_pledge_amount' => Sanitizer::MONEY,
                'max_pledge_amount' => Sanitizer::MONEY,
            ]);
    }
}
