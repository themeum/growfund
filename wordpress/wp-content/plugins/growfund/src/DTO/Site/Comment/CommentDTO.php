<?php

namespace Growfund\DTO\Site\Comment;

defined( 'ABSPATH' ) || exit;

use Growfund\DTO\DTO;
use Growfund\Constants\Site\Comment as CommentConstants;

/**
 * Unified DTO for comment operations - handles both input and output
 */
class CommentDTO extends DTO
{
    /** @var string */
    public $post_id;

    /** @var int */
    public $page = 1;

    /** @var int */
    public $per_page = 10;

    /** @var string */
    public $comment_type = CommentConstants::DEFAULT_COMMENT_TYPE;

    /** @var CommentCardItemDTO[] */
    public $comments = [];

    /** @var int */
    public $total = 0;

    /** @var bool */
    public $has_more = false;

    /** @var int */
    public $total_pages = 1;

    /** @var int */
    public $current_page = 1;

    /** @var int */
    public $total_posts = 0;
}
