<?php

namespace Growfund\DTO\Campaign;

defined( 'ABSPATH' ) || exit;

use Growfund\DTO\DTO;

class CampaignFiltersDTO extends DTO
{
    /** @var string|null */
    public $search;

    /** @var string|null */
    public $status;

    /** @var string|null */
    public $category_slug;

    /** @var string|null (format: Y-m-d or Y-m-d H:i:s) */
    public $start_date;

    /** @var string|null (format: Y-m-d or Y-m-d H:i:s) */
    public $end_date;

    /** @var int */
    public $page = 1;

    /** @var int */
    public $limit = 10;

    /** @var int|null */
    public $author_id;

    /** @var array<int> */
    public $post_ids = [];
}
