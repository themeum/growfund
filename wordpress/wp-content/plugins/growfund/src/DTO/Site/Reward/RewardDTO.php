<?php

namespace Growfund\DTO\Site\Reward;

defined( 'ABSPATH' ) || exit;

use Growfund\DTO\RewardDTO as BaseRewardDTO;

/**
 * Site Reward DTO for frontend display
 * 
 * This DTO follows best practices by being a simple data container
 * with minimal processing logic. All business logic and formatting
 * is handled in the service layer.
 */
class RewardDTO extends BaseRewardDTO
{
    /**
     * Fields that are considered not part of "meta" data.
     * Override base class to include campaign_id.
     *
     * @var array
     */
    protected static $base_fields = ['id', 'title', 'slug', 'description', 'image', 'campaign_id'];

    // Frontend-specific display fields
    /** @var string */
    public $image_src = '';

    /** @var string */
    public $image_alt = '';

    /** @var string */
    public $price = '';

    /** @var string */
    public $ships_to = '';

    /** @var string */
    public $backers = '';

    /** @var string */
    public $delivery_date = '';

    /** @var string */
    public $quantity_info = '';

    /** @var array */
    public $items = [];

    /** @var string */
    public $button_text = '';

    /** @var string */
    public $campaign_id = '';

    /** @var string */
    public $variant = 'default';
}
