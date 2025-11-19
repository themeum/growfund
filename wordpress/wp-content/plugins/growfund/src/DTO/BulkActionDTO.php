<?php

namespace Growfund\DTO;

defined( 'ABSPATH' ) || exit;

use Growfund\Sanitizer;

class BulkActionDTO extends DTO
{
    /** @var string */
    public $action;

    /** @var array */
    public $ids;

    /** @var array|null */
    public $meta;

    public static function sanitization_rules()
    {
        return [
            'ids'       => Sanitizer::ARRAY,
            'ids.*'     => Sanitizer::INT,
            'action'    => Sanitizer::TEXT,
        ];
    }
}
