<?php

namespace Growfund\Constants\Site;

defined( 'ABSPATH' ) || exit;

use Growfund\Contracts\Constant;
use Growfund\Traits\HasConstants;

/**
 * Site Comment Moderation Constants
 * 
 * Constants used for defining comment moderation policies across the site
 * 
 * @since 1.0.0
 */
class CommentModeration
{
    use HasConstants;

    /**
     * Comments need manual approval before being published
     */
    const NEED_APPROVAL = 'need-approval';

    /**
     * Comments are automatically approved and published
     */
    const AUTO_APPROVE = 'auto-approve';

    /**
     * Comments are published immediately (legacy value)
     */
    const IMMEDIATE = 'immediate';
}
