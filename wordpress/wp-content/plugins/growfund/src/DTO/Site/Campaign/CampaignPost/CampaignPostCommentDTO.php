<?php

namespace Growfund\DTO\Site\Campaign\CampaignPost;

defined( 'ABSPATH' ) || exit;

use Growfund\DTO\DTO;

class CampaignPostCommentDTO extends DTO
{
    /** @var string */
    public $author_name;

    /** @var string|null */
    public $author_image;

    /** @var string */
    public $date;

    /** @var string */
    public $content;

    /** @var int */
    public $likes = 0;

    /** @var CampaignPostCommentDTO[] */
    public $replies = [];
}
