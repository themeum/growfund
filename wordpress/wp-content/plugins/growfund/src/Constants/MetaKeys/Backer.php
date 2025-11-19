<?php

namespace Growfund\Constants\MetaKeys;

defined( 'ABSPATH' ) || exit;

use Growfund\Contracts\Constant;
use Growfund\Traits\HasConstants;

/**
 * Backer User type meta keys
 * @since 1.0.0
 */
class Backer implements Constant
{
    use HasConstants;

    /**
     * Backer meta phone key
     * @var string
     */
    const PHONE = 'phone';

    /**
     * Backer meta shipping address key
     * @var string
     */
    const SHIPPING_ADDRESS = 'shipping_address';

    /**
     * Backer meta billing address key
     * @var string
     */
    const BILLING_ADDRESS = 'billing_address';

    /**
     * Backer meta is billing address same key
     * @var string
     */
    const IS_BILLING_ADDRESS_SAME = 'is_billing_address_same';

    /**
     * Backer meta image id key
     * @var string
     */
    const IMAGE = 'image';


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
