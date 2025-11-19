<?php

namespace Growfund\DTO\Woocommerce;

defined( 'ABSPATH' ) || exit;

use Growfund\DTO\DTO;

class WoocommerceContributionDTO extends DTO
{
    /** @var string */
    public $campaign_name;

    /** @var int */
    public $campaign_id;

    /** @var float|null */
    public $contribution_amount;

    /** @var int|null */
    public $reward_id;

    /** @var float|null */
    public $bonus_support_amount;
}
