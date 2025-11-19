<?php

namespace Growfund\Constants\Site;

defined( 'ABSPATH' ) || exit;

use Growfund\Traits\HasConstants;

/**
 * Site Campaign Constants
 * 
 * Constants used in site campaign-related functionality
 * 
 * @since 1.0.0
 */
class Campaign
{
    use HasConstants;

    /**
     * Default page number for pagination
     */
    const DEFAULT_PAGE = 1;

    /**
     * No limit value for unlimited results
     */
    const NO_LIMIT = -1;

    /**
     * Default limit for campaign slider items
     */
    const DEFAULT_SLIDER_LIMIT = 12;

    /**
     * Default limit for campaign updates
     */
    const DEFAULT_UPDATES_LIMIT = 12;

    /**
     * Default limit for related campaigns
     */
    const DEFAULT_RELATED_CAMPAIGNS_LIMIT = 4;

    /**
     * Default limit for comments
     */
    const DEFAULT_COMMENTS_LIMIT = 10;

    /**
     * Default limit for campaign rewards
     */
    const DEFAULT_REWARDS_LIMIT = 6;

    /**
     * Default limit for campaign donations
     */
    const DEFAULT_DONATIONS_LIMIT = 5;

    /**
     * Default total pages for pagination
     */
    const DEFAULT_TOTAL_PAGES = 1;

    /**
     * Default current page for pagination
     */
    const DEFAULT_CURRENT_PAGE = 1;

    /**
     * Default total posts count
     */
    const DEFAULT_TOTAL_POSTS = 0;

    /**
     * Default has more flag for pagination
     */
    const DEFAULT_HAS_MORE = false;

    /**
     * Featured status value
     */
    const FEATURED_STATUS_VALUE = '1';

    /**
     * Minimum query count for relation
     */
    const MIN_QUERY_COUNT_FOR_RELATION = 1;

    /**
     * Default posts per page for single query
     */
    const DEFAULT_POSTS_PER_PAGE_SINGLE = 1;

    /**
     * Default index offset
     */
    const DEFAULT_INDEX_OFFSET = 1;

    /**
     * Default empty count
     */
    const DEFAULT_EMPTY_COUNT = 0;

    /**
     * Default empty total pages
     */
    const DEFAULT_EMPTY_TOTAL_PAGES = 0;

    /**
     * Default empty total posts
     */
    const DEFAULT_EMPTY_TOTAL_POSTS = 0;

    /**
     * Default empty total
     */
    const DEFAULT_EMPTY_TOTAL = 0;

    /**
     * Default empty count
     */
    const DEFAULT_EMPTY_COUNT_VALUE = 0;

    /**
     * Default likes count
     */
    const DEFAULT_LIKES_COUNT = 0;

    /**
     * Default comments count
     */
    const DEFAULT_COMMENTS_COUNT = 0;

    /**
     * Default likes count fallback
     */
    const DEFAULT_LIKES_COUNT_FALLBACK = 0;

    /**
     * Default comments count fallback
     */
    const DEFAULT_COMMENTS_COUNT_FALLBACK = 0;

    /**
     * Default float value
     */
    const DEFAULT_FLOAT_VALUE = 0.0;

    /**
     * Default float fallback
     */
    const DEFAULT_FLOAT_FALLBACK = 0.0;

    /**
     * Default word count for preview
     */
    const DEFAULT_WORD_COUNT_PREVIEW = 30;

    /**
     * Default parent term ID
     */
    const DEFAULT_PARENT_TERM_ID = 0;

    /**
     * Default top level parent
     */
    const DEFAULT_TOP_LEVEL_PARENT = 0;

    /**
     * Default array index
     */
    const DEFAULT_ARRAY_INDEX = 0;

    /**
     * Default parent comment ID
     */
    const DEFAULT_PARENT_COMMENT_ID = 0;

    /**
     * Sort option for newest donations
     */
    const SORT_NEWEST = 'newest';

    /**
     * Sort option for top donations
     */
    const SORT_TOP = 'top';
}
