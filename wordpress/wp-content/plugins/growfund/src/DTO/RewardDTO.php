<?php

namespace Growfund\DTO;

defined( 'ABSPATH' ) || exit;

use Growfund\CastAttributes\BooleanAttribute;
use Growfund\CastAttributes\DateTimeAttribute;
use Growfund\CastAttributes\MoneyAttribute;
use Growfund\Constants\HookNames;
use Growfund\Constants\Reward\TimeLimitType;
use Growfund\Constants\Reward\QuantityType;
use Growfund\Constants\Reward\RewardType;
use Growfund\Sanitizer;

class RewardDTO extends DTO
{
    /**
     * Fields that are considered not part of "meta" data.
     *
     * @var array
     */
    protected static $base_fields = ['id', 'title', 'slug', 'description', 'image'];

    protected $casts = [
        'amount' => MoneyAttribute::class,
        'shipping_costs.*.cost' => MoneyAttribute::class,
        'allow_local_pickup' => BooleanAttribute::class,
        'limit_start_date' => DateTimeAttribute::class,
        'limit_end_date' => DateTimeAttribute::class,
        'estimated_delivery_date' => DateTimeAttribute::class,
    ];

    /** @var string|null */
    public $id;

    /** @var string */
    public $title;

    /** @var float */
    public $amount;

    /** @var string|null */
    public $description;

    /** 
     * when inserting data
     * @var int|null
     * otherwise
     * @var \Growfund\Supports\MediaAttachment|null
     */
    public $image;

    /** @var \Growfund\Constants\Reward\QuantityType */
    public $quantity_type;

    /** @var int|null */
    public $quantity_limit;

    /** @var \Growfund\Constants\Reward\TimeLimitType */
    public $time_limit_type;

    /** @var string|null */
    public $limit_start_date;

    /** @var string|null */
    public $limit_end_date;

    /** @var \Growfund\Constants\Reward\RewardType */
    public $reward_type;

    /** @var string|null */
    public $estimated_delivery_date;

    /** @var array{location:string,cost:float}|null */
    public $shipping_costs;

    /** @var bool */
    public $allow_local_pickup;

    /** @var string|null */
    public $local_pickup_instructions;

    /** @var \Growfund\DTO\RewardItemWithQuantityDTO[] */
    public $items;

    /** @var int */
    public $reward_left;

    /** @var int */
    public $number_of_contributors;

    public static function validation_rules()
    {
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
        return apply_filters(HookNames::GROWFUND_REWARD_VALIDATION_RULES_FILTER, [
            'title' => 'required|string',
            'amount' => 'required|number',
            'description' => 'string',
            'image' => 'integer|is_valid_image_id',
            'quantity_type' => 'required|string|in:' . implode(',', QuantityType::get_constant_values()),
            'quantity_limit' => 'required_if:quantity_type,' . QuantityType::LIMITED . '|integer|min:1',
            'time_limit_type' => 'required|string|in:' . implode(',', TimeLimitType::get_constant_values()),
            'limit_start_date' => 'required_if:time_limit_type,' . TimeLimitType::SPECIFIC_DATE . '|datetime',
            'limit_end_date' => 'required_if:time_limit_type,' . TimeLimitType::SPECIFIC_DATE . '|datetime|after_date:limit_start_date',
            'reward_type' => 'required|string|in:' . implode(',', RewardType::get_constant_values()),
            'estimated_delivery_date' => 'datetime',
            'shipping_costs' => 'required_if:reward_type,' . implode(';', [RewardType::PHYSICAL_GOODS, RewardType::PHYSICAL_AND_DIGITAL_GOODS]) . '|array',
            'shipping_costs.*.location' => 'required_if:reward_type,' . implode(';', [RewardType::PHYSICAL_GOODS, RewardType::PHYSICAL_AND_DIGITAL_GOODS]) . '|string',
            'shipping_costs.*.cost' => 'required_if:reward_type,' . implode(';', [RewardType::PHYSICAL_GOODS, RewardType::PHYSICAL_AND_DIGITAL_GOODS]) . '|number',
            'allow_local_pickup' => 'boolean',
            'local_pickup_instructions' => 'string',
            'items' => 'required|array',
            'items.*.id' => 'required|integer',
            'items.*.quantity' => 'required|integer',
        ]);
    }

    public static function sanitization_rules()
    {
        return [
            'title' => Sanitizer::TEXT,
            'amount' => Sanitizer::MONEY,
            'description' => Sanitizer::TEXTAREA,
            'image' => Sanitizer::INT,
            'quantity_type' => Sanitizer::TEXT,
            'quantity_limit' => Sanitizer::INT,
            'time_limit_type' => Sanitizer::TEXT,
            'limit_start_date' => Sanitizer::DATETIME,
            'limit_end_date' => Sanitizer::DATETIME,
            'reward_type' => Sanitizer::TEXT,
            'estimated_delivery_date' => Sanitizer::DATETIME,
            'shipping_costs' => Sanitizer::ARRAY,
            'shipping_costs.*.location' => Sanitizer::TEXT,
            'shipping_costs.*.cost' => Sanitizer::MONEY,
            'allow_local_pickup' => Sanitizer::BOOL,
            'local_pickup_instructions' => Sanitizer::TEXT,
            'items' => Sanitizer::ARRAY,
            'items.*.id' => Sanitizer::INT,
            'items.*.quantity' => Sanitizer::INT,
        ];
    }
}
