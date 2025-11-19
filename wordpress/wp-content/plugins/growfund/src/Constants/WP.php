<?php

namespace Growfund\Constants;

defined( 'ABSPATH' ) || exit;

class WP
{
    /**
     * WP default posts table
     */
    const POSTS_TABLE = 'posts';

    /**
     * WP default postmeta table
     */
    const POST_META_TABLE = 'postmeta';

    /**
     * WP default users table
     */
    const USERS_TABLE = 'users';

    /**
     * WP default postmeta table
     */
    const USER_META_TABLE = 'usermeta';

    /**
     * WP Options table
     */
    const OPTIONS_TABLE = 'options';

    /**
     * WP default post metakey for thumbnail
     */
    const POST_THUMBNAIL_META_ID = '_thumbnail_id';

    /**
     * WP default action scheduler actions table
     */
    const ACTION_SCHEDULER_ACTIONS_TABLE = 'actionscheduler_actions';

    /**
     * WP default terms table
     */
    const TERM_TABLE = 'terms';

    /**
     * WP default term taxonomy table
     */
    const TERM_TAXONOMY_TABLE = 'term_taxonomy';

    /**
     * WP default term relationships table
     */
    const TERM_RELATIONSHIPS_TABLE = 'term_relationships';

    /**
     * WP default term meta table
     */
    const TERM_META_TABLE = 'termmeta';
}
