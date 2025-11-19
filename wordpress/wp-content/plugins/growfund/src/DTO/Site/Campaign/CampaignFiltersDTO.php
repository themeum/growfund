<?php

namespace Growfund\DTO\Site\Campaign;

defined( 'ABSPATH' ) || exit;

use Growfund\DTO\DTO;

class CampaignFiltersDTO extends DTO
{
    /** @var string|null */
    public $search;

    /** @var string|null */
    public $category_slug;

    /** @var string|null */
    public $tag;

    /** @var string|null */
    public $sort;

    /** @var string|null */
    public $location;

    /** @var array|null */
    public $status;

    /** @var string|null (format: Y-m-d or Y-m-d H:i:s) */
    public $start_date;

    /** @var string|null (format: Y-m-d or Y-m-d H:i:s) */
    public $end_date;

    /** @var int */
    public $page = 1;

    /** @var int */
    public $limit = 12;

    /** @var bool */
    public $featured = false;

    public $only_active = null;
}
