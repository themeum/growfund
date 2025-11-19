<?php

namespace Growfund\DTO\Campaign;

defined( 'ABSPATH' ) || exit;

use Growfund\DTO\DTO;

class BookmarkFilterDTO extends DTO
{
    /** @var string|null */
    public $search;

    /** @var int */
    public $page = 1;

    /** @var int */
    public $limit = 10;

    /** @var array<int> */
    public $post_ids = [];

    /** @var int */
    public $user_id;
}
