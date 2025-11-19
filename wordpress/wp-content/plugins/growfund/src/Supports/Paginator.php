<?php

namespace Growfund\Supports;

defined( 'ABSPATH' ) || exit;

/**
 * Class Paginator
 * @since 1.0.0
 */
class Paginator
{
    /**
     * Make pagination meta data
     * @param array $data
     * @param int $per_page
     * @param int $current_page
     * @param int $total 
     * @return array Structured response containing:
     *   - array  'results'     Formatted user data.
     *   - int    'total'       Total number of matching users.
     *   - int    'count'       Number of users on this page.
     *   - int    'per_page'    Pagination limit.
     *   - int    'current_page' Current page number.
     *   - bool   'has_more'    Whether there are more pages.
     *   - int    'overall'     Total number of records in the database.
     */
    public static function make_metadata(array $data, int $per_page, int $current_page, int $total, int $overall)
    {
        return [
            'results'      => $data,
            'total'        => $total,
            'count'        => count($data),
            'per_page'     => $per_page,
            'current_page' => $current_page,
            'has_more'     => ($current_page * $per_page) < $total,
            'overall'      => $overall,
        ];
    }
}
