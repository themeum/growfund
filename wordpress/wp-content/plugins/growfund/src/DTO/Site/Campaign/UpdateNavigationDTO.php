<?php

namespace Growfund\DTO\Site\Campaign;

defined( 'ABSPATH' ) || exit;

use Growfund\DTO\DTO;

/**
 * DTO for update navigation data
 * 
 * Contains navigation information for campaign updates (previous/next)
 */
class UpdateNavigationDTO extends DTO
{
    /** @var string */
    public $prev_id;

    /** @var string */
    public $next_id;
}
