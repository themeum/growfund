<?php

namespace Growfund\DTO\CampaignPost;

defined( 'ABSPATH' ) || exit;

use Growfund\CastAttributes\DateTimeAttribute;
use Growfund\DTO\DTO;
use Growfund\DTO\PaginatedCollectionDTO;


class CampaignPostDTO extends DTO {
    /** @var string */
    public $id;

    /** @var string */
    public $campaign_id;

    /** @var string */
    public $title;

    /** @var string */
    public $slug;

    /** @var array|null */
    public $image;

    /** @var string */
    public $description;

    /** @var string */
    public $created_by_id;

    /** @var string */
    public $created_by_name;

    /** @var string */
    public $created_by_role;

    /** @var array|null */
    public $created_by_image;

    /** @var string */
    public $created_at;

    /** @var PaginatedCollectionDTO */
    public $comments;

    /** @var int */
    public $likes = 0;


    public $casts = [
        'created_at' => DateTimeAttribute::class
    ];
}
