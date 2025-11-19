<?php

namespace Growfund\DTO;

defined( 'ABSPATH' ) || exit;

use Growfund\CastAttributes\DateTimeAttribute;

class RewardItemWithQuantityDTO extends DTO
{
    /**
     * Fields that are considered not part of "meta" data.
     *
     * @var array
     */
    protected static $base_fields = ['id', 'title', 'slug', 'description', 'image', 'created_at', 'campaign_id'];

    protected $casts = [
        'created_at' => DateTimeAttribute::class,
    ];

    /** @var string */
    public $id;

    /** @var string */
    public $title;

    /** 
     * @var \Growfund\Supports\MediaAttachment|null
     */
    public $image;

    /** @var string */
    public $campaign_id;

    /** @var int */
    public $quantity;

    /** @var string */
    public $created_at;
}
