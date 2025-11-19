<?php

namespace Growfund\DTO\Site\Campaign\CampaignPost;

defined( 'ABSPATH' ) || exit;

use Growfund\DTO\DTO;

/**
 * DTO for campaign post data with full details
 */
class CampaignPostDTO extends DTO
{
    /** @var string */
    public $id;

    /** @var int */
    public $post_id;

    /** @var string */
    public $title;

    /** @var string */
    public $slug;

    /** @var string|null */
    public $thumbnail;

    /** @var string|null */
    public $description;

    /** @var string|null (format: Y-m-d or Y-m-d H:i:s) */
    public $date;

    /** @var string|null */
    public $author_name;

    /** @var string|null */
    public $author_image;

    /** @var int */
    public $comments_count = 0;

    /** @var int */
    public $likes_count = 0;

    /**
     * @var \Growfund\DTO\Site\Campaign\CampaignPost\CampaignPostCommentDTO[]|\Growfund\DTO\Site\Comment\CommentCardItemDTO[]
     */
    public $comments = [];

    /** @var array|null */
    public $navigation;
}
