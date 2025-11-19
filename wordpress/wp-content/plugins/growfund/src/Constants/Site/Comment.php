<?php

namespace Growfund\Constants\Site;

defined( 'ABSPATH' ) || exit;

use Growfund\Contracts\Constant;
use Growfund\Traits\HasConstants;

/**
 * Site Comment Constants
 * 
 * Constants used in site comment-related functionality
 * 
 * @since 1.0.0
 */
class Comment
{
    use HasConstants;

    /**
     * Comment type for update comments
     */
    const TYPE_UPDATE_COMMENT = 'update_comment';

    /**
     * Comment type for campaign comments
     */
    const TYPE_CAMPAIGN_COMMENT = 'comment';

    /**
     * Default comment type
     */
    const DEFAULT_COMMENT_TYPE = 'comment';

    /**
     * Approved comment status
     */
    const STATUS_APPROVED = 'approve';

    /**
     * Top-level comments only (exclude replies)
     */
    const TOP_LEVEL_ONLY = 0;

    /**
     * Default page number for pagination
     */
    const DEFAULT_PAGE = 1;

    /**
     * Default parent ID for top-level comments
     */
    const DEFAULT_PARENT_ID = 0;

    /**
     * Comment approved status value
     */
    const COMMENT_APPROVED_STATUS = 1;

    /**
     * Comment unapproved status value
     */
    const COMMENT_UNAPPROVED_STATUS = 0;

    /**
     * Default total count for empty results
     */
    const DEFAULT_TOTAL = 0;

    /**
     * Default replies count for comments
     */
    const DEFAULT_REPLIES_COUNT = 0;

    /**
     * Threshold for user ID validation
     */
    const USER_ID_CHECK_THRESHOLD = 0;

    /**
     * Disabled setting value for comments
     */
    const DISABLED_SETTING_VALUE = '0';

    /**
     * Default per page count for pagination
     */
    const DEFAULT_PER_PAGE = 10;

    /**
     * Maximum per page count for pagination
     */
    const MAX_PER_PAGE = 50;
}
