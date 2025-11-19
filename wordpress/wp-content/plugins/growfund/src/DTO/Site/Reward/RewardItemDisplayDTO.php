<?php

namespace Growfund\DTO\Site\Reward;

defined( 'ABSPATH' ) || exit;

use Growfund\DTO\DTO;

/**
 * Reward Item Display DTO for frontend
 * 
 * This DTO is specifically designed for displaying reward items
 * in the frontend with formatted data.
 */
class RewardItemDisplayDTO extends DTO
{
    /** @var string */
    public $name = '';

    /** @var string */
    public $quantity = '';

    /** @var string */
    public $image_src = '';

    /** @var string */
    public $image_alt = '';
}
