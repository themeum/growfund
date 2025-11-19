<?php

namespace Growfund\Constants\MetaKeys;

defined( 'ABSPATH' ) || exit;

use Growfund\Contracts\Constant;
use Growfund\Traits\HasConstants;

/**
 * Fundraiser User type meta keys
 * @since 1.0.0
 */
class Fundraiser implements Constant
{
    use HasConstants;

    /**
     * Fundraiser meta phone key
     * @var string
     */
    const PHONE = 'phone';

    /**
     * Fundraiser meta image id key
     * @var string
     */
    const IMAGE = 'image';

    /**
     * Fundraiser meta status key
     * @var string
     */
    const STATUS = 'status';

    /**
     * Fundraiser meta decline reason key
     * @var string
     */
    const DECLINE_REASON = 'decline_reason';

    /**
     * Fundraiser meta joining date key
     * @var string
     */
    const JOINED_AT = 'joined_at';


    /**
     * Get all Fundraiser meta keys
     * 
     * @return array
     */
    public static function all()
    {
        return static::get_constant_values();
    }
}
