<?php

namespace Growfund\DTO;

defined( 'ABSPATH' ) || exit;

class PaginatedCollectionDTO extends DTO
{
    /** @var array|null */
    public $results;
    /** @var int */
    public $count;
    /** @var int */
    public $total;
    /** @var int */
    public $current_page;
    /** @var int */
    public $per_page;
    /** @var bool */
    public $has_more;
    /** @var int */
    public $overall;

    /**
     * Get the default values if there is no data provided.
     *
     * @return array
     */
    public static function defaults()
    {
        return [
            'results' => [],
            'total' => 0,
            'count' => 0,
            'per_page' => 0,
            'current_page' => 0,
            'has_more' => false,
            'overall' => 0,
        ];
    }

    public function get_casts()
    {
        return [
            'results.*' => DTO::class,
        ];
    }
}
