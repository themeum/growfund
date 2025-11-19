<?php

namespace Growfund\Constants\MetaKeys;

defined( 'ABSPATH' ) || exit;

use Growfund\Contracts\Constant;
use Growfund\Traits\HasConstants;

/**
 * Reward Post type meta keys
 * @since 1.0.0
 */
class Reward implements Constant
{
    use HasConstants;

    /**
     * Reward meta amount key
     * @var string
     */
    const AMOUNT = 'amount';

    /**
     * Reward meta quantity type key
     * @var string
     */
    const QUANTITY_TYPE = 'quantity_type';

    /**
     * Reward meta quantity limit key
     * @var string
     */
    const QUANTITY_LIMIT = 'quantity_limit';

    /**
     * Reward meta time limit type key
     * @var string
     */
    const TIME_LIMIT_TYPE = 'time_limit_type';

    /**
     * Reward meta limit start date key
     * @var string
     */
    const LIMIT_START_DATE = 'limit_start_date';

    /**
     * Reward meta limit end date key
     * @var string
     */
    const LIMIT_END_DATE = 'limit_end_date';

    /**
     * Reward meta reward type key
     * @var string
     */
    const REWARD_TYPE = 'reward_type';

    /**
     * Reward meta estimated delivery date key
     * @var string
     */
    const ESTIMATED_DELIVERY_DATE = 'estimated_delivery_date';

    /**
     * Reward meta shipping costs key
     * @var string
     */
    const SHIPPING_COSTS = 'shipping_costs';

    /**
     * Reward meta allow local pickup key
     * @var string
     */
    const ALLOW_LOCAL_PICKUP = 'allow_local_pickup';

    /**
     * Reward meta local pickup instructions key
     * @var string
     */
    const LOCAL_PICKUP_INSTRUCTIONS = 'local_pickup_instructions';

    /**
     * Reward meta items key
     * @var string
     */
    const ITEMS = 'items';


    /**
     * Get all backer meta keys
     * 
     * @return array
     */
    public static function all()
    {
        return static::get_constant_values();
    }
}
