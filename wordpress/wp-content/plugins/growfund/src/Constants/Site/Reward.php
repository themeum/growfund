<?php

namespace Growfund\Constants\Site;

defined( 'ABSPATH' ) || exit;

use Growfund\Contracts\Constant;
use Growfund\Traits\HasConstants;

/**
 * Site Reward Constants
 * 
 * Constants used in site reward-related functionality
 * 
 * @since 1.0.0
 */
class Reward
{
    use HasConstants;

    /**
     * Default page number for pagination
     */
    const DEFAULT_PAGE = 1;

    /**
     * Default number of comments per page
     */
    const DEFAULT_COMMENTS_PER_PAGE = 10;

    /**
     * First index in arrays
     */
    const FIRST_INDEX = 0;

    /**
     * Index offset for navigation
     */
    const INDEX_OFFSET = 1;

    /**
     * Short date format (e.g., "Jan 15")
     */
    const DATE_FORMAT_SHORT = 'M j';

    /**
     * Long date format (e.g., "January 15, 2024")
     */
    const DATE_FORMAT_LONG = 'F j, Y';
}
