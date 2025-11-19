<?php

namespace Growfund\DTO\Site\Campaign;

defined( 'ABSPATH' ) || exit;

use Growfund\DTO\DTO;
use Growfund\DTO\Site\Comment\CommentCardItemDTO;
use Growfund\DTO\Site\Comment\CommentFormDTO;
use Growfund\DTO\Site\Campaign\UpdateNavigationDTO;

/**
 * DTO for single campaign update details with comments and navigation
 * 
 * Contains complete update information including comments and navigation
 */
class CampaignUpdateDetailsDTO extends DTO
{
    /** @var string */
    public $id;

    /** @var string */
    public $post_id;

    /** @var string */
    public $title;

    /** @var string */
    public $description;

    /** @var string */
    public $slug;

    /** @var string */
    public $thumbnail;

    /** @var string */
    public $date;

    /** @var string */
    public $author_name;

    /** @var string */
    public $author_image;

    /** @var int */
    public $comments_count = 0;

    /** @var int */
    public $likes_count = 0;

    /** @var int */
    public $total_comments_count = 0;

    /** @var CommentFormDTO */
    public $comment_form_data;

    /** @var CommentCardItemDTO[] */
    public $comments;

    /** @var UpdateNavigationDTO */
    public $navigation;

    /** @var string */
    public $update_id;

    /** @var string */
    public $update_title;

    /** @var string */
    public $update_date;

    /** @var string */
    public $update_image;

    /** @var string */
    public $update_image_alt;

    /** @var string */
    public $update_content_preview;

    /** @var string */
    public $update_content_full;

    /** @var int */
    public $update_position;

    /** @var array */
    public $update_creator = [];

    /** @var array */
    public $update_stats = [];

    /** @var string */
    public $update_badge;
}
