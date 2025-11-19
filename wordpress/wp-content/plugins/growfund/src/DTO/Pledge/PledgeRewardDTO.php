<?php

namespace Growfund\DTO\Pledge;

defined( 'ABSPATH' ) || exit;

use Growfund\CastAttributes\MoneyAttribute;
use Growfund\DTO\DTO;

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
    ];

    /** @var string */
    public $id;

    /** @var string */
    public $title;

    /** @var string */
    public $description;

    /** @var \Growfund\DTO\RewardItemDTO[] */
    public $items;

    /** @var float */
    public $amount;

    /** @var \Growfund\Supports\MediaAttachment */
    public $image;
}
