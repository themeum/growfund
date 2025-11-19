<?php

namespace Growfund\DTO\Site\Campaign;

defined( 'ABSPATH' ) || exit;

use Growfund\DTO\DTO;
use Growfund\DTO\Site\Comment\CommentCardItemDTO;
use Growfund\DTO\Site\Campaign\UpdateNavigationDTO;

/**
 * DTO for individual campaign update with frontend display fields
 */
class CampaignUpdateDTO extends DTO
{
    /** @var string */
    public $update_id;

    /** @var string */
    public $update_post_id;

    /** @var string */
    public $post_id;

    /** @var string */
    public $update_badge;

    /** @var string */
    public $update_date;

    /** @var string */
    public $update_title;

    /** @var string */
    public $update_image;

    /** @var string */
    public $update_image_alt;

    /** @var string */
    public $update_content_preview;

    /** @var int */
    public $update_position;

    /** @var array */
    public $update_creator;

    /** @var array */
    public $update_stats;

    /** @var string */
    public $id;

    /** @var string */
    public $badge;

    /** @var string */
    public $date;

    /** @var string */
    public $title;

    /** @var string */
    public $image;

    /** @var string */
    public $image_alt;

    /** @var string */
    public $content_preview;

    /** @var string */
    public $content_full;

    /** @var int */
    public $position;

    /** @var array */
    public $creator;

    /** @var array */
    public $stats;

    /** @var UpdateNavigationDTO */
    public $navigation;

    /** @var CommentCardItemDTO[] */
    public $comments;

    /** @var array */
    public $comment_form_data;
}
