<?php

namespace Growfund\Supports;

defined( 'ABSPATH' ) || exit;

/**
 * Class to handle the post meta
 *
 * Provides static methods for getting, setting, updating, and deleting post meta.
 *
 * @since 1.0.0
 */
class PostMeta
{
    /**
     * Retrieve post meta by ID and key.
     *
     * @param int $post_id
     * @param string $key
     * @param bool $single
     *
     * @return mixed
     */
    public static function get(int $post_id, string $key = '', bool $single = true)
    {
        if (!empty($key)) {
            return maybe_unserialize(get_post_meta($post_id, growfund_with_prefix($key), $single));
        }

        $metadata = get_post_meta($post_id, $key, $single);

        $updated_metadata = [];

        foreach ($metadata as $key => $value) {
            $key = growfund_without_prefix($key);
            $updated_metadata[$key] = $single ? maybe_unserialize($value[0]) : maybe_unserialize($value);
        }

        return $updated_metadata;
    }

    /**
     * Get all post meta by ID.
     *
     * @param int $post_id
     * @param mixed  $value
     *
     * @return array
     */
    public static function get_all(int $post_id, $single = true)
    {
        return self::get($post_id, '', $single);
    }

    /**
     * Add the value of a specific post meta.
     *
     * @param int $post_id
     * @param mixed  $value
     *
     * @return bool|int
     */
    public static function add(int $post_id, string $key, $value, $unique = false)
    {
        return add_post_meta($post_id, growfund_with_prefix($key), $value, $unique);
    }

    /**
     * Add multiple post meta at once.
     *
     * @param int $post_id
     * @param array $meta_input
     *
     * @return bool
     */
    public static function add_many(int $post_id, array $meta_input)
    {
        foreach ($meta_input as $key => $value) {
            static::add($post_id, $key, $value);
        }

        return true;
    }

    /**
     * Update the value of a specific post meta.
     *
     * @param int $post_id
     * @param mixed  $value
     *
     * @return bool|int
     */
    public static function update(int $post_id, string $key, $value, $prev_value = '')
    {
        return update_post_meta($post_id, growfund_with_prefix($key), $value, $prev_value);
    }

    /**
     * Update multiple post meta at once.
     *
     * @param int $post_id
     * @param array $meta_input
     *
     * @return bool
     */
    public static function update_many(int $post_id, array $meta_input)
    {
        foreach ($meta_input as $key => $value) {
            static::update($post_id, $key, $value);
        }

        return true;
    }

    /**
     * Delete a specific post meta.
     *
     * @param int $post_id
     *
     * @return bool
     */
    public static function delete(int $post_id, string $key, $value = '')
    {
        return delete_post_meta($post_id, growfund_with_prefix($key), $value);
    }
}
