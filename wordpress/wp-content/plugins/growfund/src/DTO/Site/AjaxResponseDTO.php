<?php

namespace Growfund\DTO\Site;

defined( 'ABSPATH' ) || exit;

use Growfund\DTO\DTO;

/**
 * Standardized AJAX Response DTO
 * 
 * Provides consistent structure for all AJAX responses
 */
class AjaxResponseDTO extends DTO
{
    /** @var string */
    public $html = '';

    /** @var bool */
    public $has_more = false;

    /** @var int */
    public $total = 0;

    /** @var int */
    public $total_pages = 0;

    /** @var int */
    public $current_page = 1;

    /** @var int */
    public $total_posts = 0;

    /** @var array */
    public $additional_data = [];
}
