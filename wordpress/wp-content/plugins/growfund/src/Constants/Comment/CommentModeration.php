<?php

namespace Growfund\Constants\Comment;

use Growfund\Traits\HasConstants;

defined( 'ABSPATH' ) || exit;

class CommentModeration {
    use HasConstants;

    /**
     * Comments need manual approval before being published
     */
    const NEED_APPROVAL = 'need-approval';


    /**
     * Comments are published immediately
     */
    const IMMEDIATE = 'immediate';
}
