<?php

namespace Growfund\Constants\Comment;

use Growfund\Traits\HasConstants;

defined( 'ABSPATH' ) || exit;

class CommentVisibility {
    use HasConstants;

    const PUBLIC = 'public';

    const CONTRIBUTORS = 'contributors';

    const LOGGED_IN_USER = 'logged-in-users';
}
