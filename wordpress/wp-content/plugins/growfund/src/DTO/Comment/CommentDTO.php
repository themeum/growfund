<?php

namespace Growfund\DTO\Comment;

defined( 'ABSPATH' ) || exit;

use Growfund\DTO\DTO;
use Growfund\DTO\PaginatedCollectionDTO;

class CommentDTO extends DTO
{
    /** @var string */
    public $id;

    /** @var string */
    public $post_id;

    /** @var string */
    public $comment_parent;

    /** @var string */
    public $author_id;

    /** @var string */
    public $author_name;

    /** @var string|null */
    public $author_image;

    /** @var string */
    public $created_at;

    /** @var string */
    public $content;

    /** @var string */
    public $comment_type;

    /** @var bool */
    public $comment_approved;

    /** @var PaginatedCollectionDTO */
    public $replies = [];

    public $casts = [
        'replies.results.*' => self::class,
    ];
}
