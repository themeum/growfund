<?php

namespace Growfund\DTO;

defined( 'ABSPATH' ) || exit;

use Growfund\CastAttributes\DateTimeAttribute;
use Growfund\Constants\RewardItem\AssetType;
use Growfund\Constants\RewardItem\RewardItemType;
use Growfund\Sanitizer;

class RewardItemDTO extends DTO
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
     * when inserting data
     * @var int|null
     * otherwise
     * @var \Growfund\Supports\MediaAttachment|null
     */
    public $image;

    /** @var string */
    public $campaign_id;

    /** @var string */
    public $created_at;

    /** @var string */
    public $type;

    /** @var string|null */
    public $asset_type;

    /** @var \Growfund\Supports\MediaAttachment|int|null */
    public $asset;

    /** @var string|null */
    public $asset_url;

    public $can_download = false;

    public static function validation_rules()
    {
        return [
            'type' => 'required|string|in:' . implode(',', RewardItemType::get_constant_values()),
            'title' => 'required|string',
            'image' => 'integer|is_valid_image_id',
            'asset_type' =>[
                'required_if:type,' . RewardItemType::DIGITAL,
                'prohibited_if:type,' . RewardItemType::PHYSICAL,
                'string',
                'in:' . implode(',', AssetType::get_constant_values()),
            ],
            'asset' => 'required_if:asset_type,' . AssetType::FILE . '|integer|prohibited_if:type,' . AssetType::URL,
            'asset_url' => 'required_if:asset_type,' . AssetType::URL . '|url|prohibited_if:type,' . AssetType::FILE,
        ];
    }

    public static function sanitization_rules()
    {
        return [
            'campaign_id' => Sanitizer::INT,
            'type' => Sanitizer::TEXT,
            'title' => Sanitizer::TEXT,
            'image' => Sanitizer::INT,
            'asset_type' => Sanitizer::TEXT,
            'asset' => Sanitizer::INT,
            'asset_url' => Sanitizer::TEXT,
        ];
    }
}
