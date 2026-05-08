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

    /** @var string|null (format: Y-m-d or Y-m-d H:i:s) */
    public $start_date;

    /** @var string|null (format: Y-m-d or Y-m-d H:i:s) */
    public $end_date;

    /** @var int */
    public $page = 1;

    /** @var int */
    public $limit = 10;

    /** @var int|null */
    public $fundraiser_id;

    /** @var int|null */
    public $collaborator_id;

    /** @var array<int> */
    public $post_ids = [];

    /** 
     * @since 1.0.6
     * @var array<int> 
     */
    public $post__not_in_ids = [];

    /** 
     * @since 1.0.4
     * @var string|null 
     */
    public $category_slug;

    /**
     * @since 1.0.6
     * @var string|array|null
     */
    public $tag;

    /** 
     * @since 1.0.4
     * @var int|null - 1|0 
     */
    public $is_featured;

    /** 
     * @since 1.0.4
     * @var string|null -- 'created_date' | 'end_date' | 'start_date'
     */
    public $orderby;

    /** 
     * @since 1.0.4
     * @var string|null -- 'asc'|'desc'
     */
    public $order;
}
