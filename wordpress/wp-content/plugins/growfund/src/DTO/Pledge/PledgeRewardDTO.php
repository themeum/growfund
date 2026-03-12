<?php

namespace Growfund\DTO\Pledge;

defined( 'ABSPATH' ) || exit;

use Growfund\CastAttributes\MoneyAttribute;
use Growfund\DTO\DTO;
use Growfund\DTO\RewardItemWithQuantityDTO;

/**
 * Data Transfer Object for PledgeCampaign
 *
 * @since 1.0.0
 */
class PledgeRewardDTO extends DTO
{
    /**
     * Fields that are considered not part of "meta" data.
     *
     * @var array
     */
    protected static $base_fields = [];

    protected $casts = [
        'amount' => MoneyAttribute::class,
        'items' => RewardItemWithQuantityDTO::class
    ];

    /** @var string */
    public $id;

    /** @var string */
    public $title;

    /** @var string */
    public $description;

    /** @var RewardItemWithQuantityDTO[] */
    public $items;

    /** @var float */
    public $amount;

    /** @var \Growfund\Supports\MediaAttachment */
    public $image;

    /** @var value-of<\Growfund\Constants\Reward\RewardType> */
    public $reward_type;

    /** @var string|null */
    public $estimated_delivery_date;

    /** @var bool */
    public $allow_local_pickup;

    /** @var string|null */
    public $local_pickup_instructions;
}
