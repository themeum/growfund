<?php

namespace Growfund\Constants\MetaKeys;

defined( 'ABSPATH' ) || exit;

use Growfund\Contracts\Constant;
use Growfund\Traits\HasConstants;

/**
 * Donor User type meta keys
 * @since 1.0.0
 */
class Donor implements Constant
{
    use HasConstants;

    /**
     * Donor meta phone key
     * @var string
     */
    const PHONE = 'phone';

    /**
     * Donor meta image id key
     * @var string
     */
    const IMAGE = 'image';

    /**
     * Donor meta status key
     * @var string
     */
    const STATUS = 'status';

    /**
     * Donor meta address key
     * @var string
     */
    const ADDRESS = 'billing_address';


    /**
     * Get all Donor meta keys
     * 
     * @return array
     */
    public static function all()
    {
        return static::get_constant_values();
    }
}
