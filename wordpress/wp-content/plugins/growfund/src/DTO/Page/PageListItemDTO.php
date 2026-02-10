<?php

namespace Growfund\DTO\Page;

defined( 'ABSPATH' ) || exit;

use Growfund\DTO\DTO;

class PageListItemDTO extends DTO
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $slug;

    /**
     * @var string
     */
    public $url;

    /**
     * @var string
     */
    public $status;

    /**
     * @var string
     */
    public $parent_id;

    /**
     * @var bool
     */
    public $is_growfund_page; 

    /**
     * @var string|null
     */
    public $page_key;
}
