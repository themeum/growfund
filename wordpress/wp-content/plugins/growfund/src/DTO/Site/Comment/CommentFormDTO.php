<?php

namespace Growfund\DTO\Site\Comment;

defined( 'ABSPATH' ) || exit;

use Growfund\DTO\DTO;
use Growfund\Constants\Site\Comment as CommentConstants;

/**
 * Unified DTO for comment form and template data
 */
class CommentFormDTO extends DTO
{
    // Form fields
    /** @var bool */
    public $allow_comments = false;

    /** @var string */
    public $comment_form_html;

    /** @var string */
    public $comment_post_id;

    /** @var string */
    public $comment_type = CommentConstants::DEFAULT_COMMENT_TYPE;

    /** @var string */
    public $placeholder;

    /** @var string */
    public $button_text;

    /** @var bool */
    public $is_reply = false;

    /** @var string */
    public $reply_to_author;

    /** @var string */
    public $parent_id;

    // User authentication fields
    /** @var bool */
    public $is_logged_in = false;

    /** @var string */
    public $current_user_name;

    /** @var string */
    public $current_user_avatar;

    // Template fields
    /** @var string */
    public $comment_id;

    /** @var string */
    public $post_id;

    /** @var string */
    public $author_name;

    /** @var string */
    public $comment_data_json;

    /** @var CommentCardItemDTO|null */
    public $comment;

    /** @var bool */
    public $nested = false;

    /** @var bool */
    public $showMainWrapper = false;

    /** @var bool */
    public $isAjaxResponse = true;
}
