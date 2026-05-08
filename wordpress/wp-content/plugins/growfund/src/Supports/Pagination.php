<?php

namespace Growfund\Supports;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\UserTypes\Fundraiser;
use WP_Query;
use WP_User_Query;

/**
 * Class to handle the post meta
 *
 * Provides static methods for getting, setting, updating, and deleting post meta.
 *
 * @since 1.0.0
 */
class Pagination
{
    /**
     * Get the overall post count for a specific post type without any filters.
     * This will be required to detect the overall posts count.
     *
     * @param string $post_type
     * @return int
     */
    public static function get_overall_post_count(string $post_type, array $args = [])
    {
        $args = array_merge([
            'post_type'   => $post_type,
            'fields'      => 'ids',
            'post_status'    => ['publish', 'draft', 'pending', 'future', 'private', 'trash'],
            'posts_per_page' => 1,
        ], $args);

        $query = new WP_Query($args);

        return $query->found_posts;
    }

    /**
     * Get the overall user record count for a specific user role.
     *
     * @param string|null $role
     * @return int
     */
    public static function get_overall_user_count(?string $role = null)
    {
        $args = [
            'count_total' => true,
            'number' => -1,
        ];

        if (!is_null($role)) {
            $args['role'] = $role;
        }

        if ($role === Fundraiser::ROLE && growfund_user()->is_fundraiser()) {
            $args['exclude'] = [growfund_user()->get_id()]; // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude
        }

        $query = new WP_User_Query($args);

        return intval($query->get_total() ?? 0);
    }
}
