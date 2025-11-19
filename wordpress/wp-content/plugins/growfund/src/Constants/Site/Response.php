<?php

namespace Growfund\Constants\Site;

defined( 'ABSPATH' ) || exit;

use Growfund\Traits\HasConstants;

/**
 * Site Response Constants
 * 
 * Constants used in site response-related functionality
 * 
 * @since 1.0.0
 */
class Response
{
    use HasConstants;

    /**
     * Default empty HTML content
     */
    const DEFAULT_HTML = '';

    /**
     * Default has more flag for pagination
     */
    const DEFAULT_HAS_MORE = false;

    /**
     * Default total count
     */
    const DEFAULT_TOTAL = 0;

    /**
     * Default total pages count
     */
    const DEFAULT_TOTAL_PAGES = 0;

    /**
     * Default current page number
     */
    const DEFAULT_CURRENT_PAGE = 1;

    /**
     * Default total posts count
     */
    const DEFAULT_TOTAL_POSTS = 0;

    /**
     * Default additional data array
     */
    const DEFAULT_ADDITIONAL_DATA = [];
}
