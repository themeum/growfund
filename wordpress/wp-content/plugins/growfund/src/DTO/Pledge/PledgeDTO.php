<?php

namespace Growfund\DTO\Pledge;

defined( 'ABSPATH' ) || exit;

use Growfund\CastAttributes\BooleanAttribute;
use Growfund\CastAttributes\DateTimeAttribute;
use Growfund\DTO\DTO;

/**
 * Data Transfer Object for Pledge
 *
 * @since 1.0.0
 */
class PledgeDTO extends DTO
{
    /**
     * Fields that are considered not part of "meta" data.
     *
     * @var array
     */
    protected static $base_fields = [];

    protected $casts = [
        'is_manual' => BooleanAttribute::class,
        'created_at' => DateTimeAttribute::class,
        'updated_at' => DateTimeAttribute::class,
        'payment' => PledgePaymentDTO::class,
        'campaign' => PledgeCampaignDTO::class,
        'backer' => PledgeBackerDTO::class,
        'reward' => PledgeRewardDTO::class
    ];

    /** @var string */
    public $id;

    /** @var string */
    public $uid;

    /** @var string */
    public $status;

    /** @var bool */
    public $is_manual;

    /** @var string */
    public $pledge_option;

    /** @var \Growfund\Constants\Pledge\DeliveryOption */
    public $delivery_option;

    /** @var bool */
    public $is_ready_for_pickup = false;

    /** @var string|null */
    public $notes;

    /** @var string */
    public $created_at;

    /** @var string */
    public $updated_at;

    /** @var \Growfund\DTO\Pledge\PledgePaymentDTO */
    public $payment;

    /** @var \Growfund\DTO\Pledge\PledgeCampaignDTO */
    public $campaign;

    /** @var \Growfund\DTO\Pledge\PledgeBackerDTO */
    public $backer;

    /** @var \Growfund\DTO\Pledge\PledgeRewardDTO */
    public $reward;
}
