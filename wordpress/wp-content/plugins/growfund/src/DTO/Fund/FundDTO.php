<?php

namespace Growfund\DTO\Fund;

defined( 'ABSPATH' ) || exit;

use Growfund\CastAttributes\BooleanAttribute;
use Growfund\CastAttributes\DateTimeAttribute;
use Growfund\Constants\Status\FundStatus;
use Growfund\DTO\DTO;
use Growfund\Sanitizer;

class FundDTO extends DTO
{
    /**
     * Fields that are considered not part of "meta" data.
     *
     * @var array
     */
    protected static $base_fields = [];

    protected $casts = [
        'is_default' => BooleanAttribute::class,
        'created_at' => DateTimeAttribute::class,
        'updated_at' => DateTimeAttribute::class,
    ];

    public $id;

    public $title;

    public $description;

    public $is_default;

    public $status;

    public $created_at;

    public $created_by;

    public $updated_at;

    public $updated_by;

    public static function validation_rules()
    {
        return [
            'title' => 'required|string',
            'description' => 'string|nullable'
        ];
    }

    public static function sanitization_rules()
    {
        return [
            'title' => Sanitizer::TEXT,
            'description' => Sanitizer::TEXTAREA,
        ];
    }
}
