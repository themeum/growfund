<?php

namespace Growfund\DTO\Site;

defined( 'ABSPATH' ) || exit;

use Growfund\DTO\DTO;
use Growfund\DTO\RewardDTO;
use Growfund\DTO\Site\OrderSummaryDTO;

/**
 * Checkout Data Transfer Object for site checkout functionality
 *
 * @since 1.0.0
 */
class CheckoutDTO extends DTO
{
    /** @var int|null */
    public $campaign_id;

    /** @var int|null */
    public $reward_id;

    /** @var RewardDTO|null */
    public $reward_data;

    /** @var OrderSummaryDTO */
    public $order_summary;

    /** @var string */
    public $consent_text;

    /** @var array|null */
    public $user_shipping_address;

    /** @var string|null */
    public $campaign_slug;
}
