<?php

namespace Growfund\DTO\Site;

defined( 'ABSPATH' ) || exit;

use Growfund\DTO\DTO;
use Growfund\CastAttributes\MoneyAttribute;

/**
 * Order Summary Data Transfer Object for checkout functionality
 *
 * @since 1.0.0
 */
class OrderSummaryDTO extends DTO
{
    protected $casts = [
        'bonus_support' => MoneyAttribute::class,
        'reward_price' => MoneyAttribute::class,
        'shipping' => MoneyAttribute::class,
        'total' => MoneyAttribute::class,
    ];

    /** @var float */
    public $bonus_support;

    /** @var float */
    public $reward_price;

    /** @var float */
    public $shipping;

    /** @var float */
    public $total;
}
