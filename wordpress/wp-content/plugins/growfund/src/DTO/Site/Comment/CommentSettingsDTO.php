<?php

namespace Growfund\DTO\Site\Comment;

defined( 'ABSPATH' ) || exit;

use Growfund\DTO\DTO;
use Growfund\Constants\Site\Comment as CommentConstants;

/**
 * DTO for comment settings and permissions
 */
class CommentSettingsDTO extends DTO
{
    /** @var bool */
    public $allow_comments = false;

    /** @var int */
    public $post_id = 0;

    /** @var string */
    public $comment_type = CommentConstants::DEFAULT_COMMENT_TYPE;

    /** @var bool */
    public $is_global_setting = false;

    /** @var bool */
    public $is_individual_setting = false;
}
