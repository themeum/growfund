<?php

namespace Growfund\DTO\Site\Comment;

defined( 'ABSPATH' ) || exit;

use Growfund\DTO\DTO;

class CommentCardItemDTO extends DTO
{
    /** @var string */
    public $id;

    /** @var string */
    public $author;

    /** @var string */
    public $avatar;

    /** @var string */
    public $time;

    /** @var string */
    public $text;

    /** @var int */
    public $likes = 0;

    /** @var int */
    public $replies_count = 0;

    /** @var int|null */
    public $parent_id;

    /** @var int|null */
    public $user_id;

    /** @var bool */
    public $can_reply = true;

    /** @var bool */
    public $can_delete = false;

    /** @var bool */
    public $user_has_liked = false;

    /** @var string */
    public $comment_type;

    /** @var bool */
    public $is_awaiting_approval = false;

    /** @var array */
    public $replies = [];
}
