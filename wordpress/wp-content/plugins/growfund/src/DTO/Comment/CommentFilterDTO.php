<?php

namespace Growfund\DTO\Comment;

defined( 'ABSPATH' ) || exit;



class CommentFilterDTO {
    /** @var int */
    public $page = 1;

    /** @var int */
    public $limit = 2;

    /** @var int */
    public $parent = 0;

    /** @var int */
    public $post_id;

    /** @var string */
    public $orderby = 'date';

    /** @var string */
    public $order = 'DESC';

    /** @var string 'hold','spam,'trash','approve','all' */
    public $status = 'approve';

    /** @var string 'comment','update_comment' */
    public $comment_type = 'comment';
}
