<?php

namespace Growfund\Supports;

defined( 'ABSPATH' ) || exit;

use Growfund\Contracts\Registrable;

class Terms
{
    /**
     * Get the terms for a post.
     * 
     * @param int $post_id
     * @param string $taxonomy_name
     *
     * @return array
     */
    public static function get_terms(int $post_id, string $taxonomy_name)
    {
        $terms = wp_get_post_terms($post_id, $taxonomy_name, ['fields' => 'all']);

        if (is_wp_error($terms)) {
            return [];
        }

        return $terms;
    }

    /**
     * Get the first term for a post.
     * 
     * @param int $post_id
     * @param string $taxonomy_name
     * 
     * @return \WP_Term|null
     */
    public static function get_term(int $post_id, string $taxonomy_name)
    {
        $terms = static::get_terms($post_id, $taxonomy_name);

        return $terms[0] ?? null;
    }

    /**
     * Get the term ID for a post.
     * 
     * @param int $post_id
     * @param string $taxonomy_name
     * 
     * @return int|null
     */
    public static function get_term_id(int $post_id, string $taxonomy_name)
    {
        $term = static::get_term($post_id, $taxonomy_name);

        return $term->term_id ?? null;
    }

    /**
     * Get array of term ids for a post.
     * 
     * @param int $post_id
     * @param string $taxonomy_name
     * 
     * @return array
     */
    public static function get_term_ids(int $post_id, string $taxonomy_name)
    {
        $ids = wp_get_post_terms($post_id, $taxonomy_name, ['fields' => 'ids']) ?? [];

        if (is_wp_error($ids)) {
            return [];
        }

        return $ids;
    }
}
