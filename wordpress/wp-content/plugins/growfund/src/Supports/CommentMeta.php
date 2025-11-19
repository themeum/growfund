<?php

namespace Growfund\Supports;

defined( 'ABSPATH' ) || exit;

/**
 * Class to handle comment meta
 *
 * Provides static methods for getting, setting, updating, and deleting comment meta.
 *
 * @since 1.0.0
 */
class CommentMeta
{
    /**
     * Retrieve comment meta by comment ID and key.
     *
     * @param int $comment_id
     * @param string $key
     * @param bool $single
     *
     * @return mixed
     */
    public static function get(int $comment_id, string $key = '', bool $single = true)
    {
        if (!empty($key)) {
            return maybe_unserialize(get_comment_meta($comment_id, growfund_with_prefix($key), $single));
        }

        $metadata = get_comment_meta($comment_id, $key, $single);
        $updated_metadata = [];

        foreach ($metadata as $meta_key => $value) {
            $key = growfund_without_prefix($meta_key);
            $updated_metadata[$key] = $single ? maybe_unserialize($value[0]) : maybe_unserialize($value);
        }

        return $updated_metadata;
    }

    /**
     * Get all comment meta by ID.
     *
     * @param int $comment_id
     * @param bool $single
     *
     * @return array
     */
    public static function get_all(int $comment_id, bool $single = true)
    {
        return self::get($comment_id, '', $single);
    }

    /**
     * Add the value of a specific comment meta.
     *
     * @param int $comment_id
     * @param string $key
     * @param mixed $value
     * @param bool $unique
     *
     * @return bool|int
     */
    public static function add(int $comment_id, string $key, $value, bool $unique = false)
    {
        return add_comment_meta($comment_id, growfund_with_prefix($key), $value, $unique);
    }

    /**
     * Add multiple comment meta at once.
     *
     * @param int $comment_id
     * @param array $meta_input
     *
     * @return bool
     */
    public static function add_many(int $comment_id, array $meta_input)
    {
        foreach ($meta_input as $key => $value) {
            static::add($comment_id, $key, $value);
        }

        return true;
    }

    /**
     * Update the value of a specific comment meta.
     *
     * @param int $comment_id
     * @param string $key
     * @param mixed $value
     * @param mixed $prev_value
     *
     * @return bool|int
     */
    public static function update(int $comment_id, string $key, $value, $prev_value = '')
    {
        return update_comment_meta($comment_id, growfund_with_prefix($key), $value, $prev_value);
    }

    /**
     * Update multiple comment meta at once.
     *
     * @param int $comment_id
     * @param array $meta_input
     *
     * @return bool
     */
    public static function update_many(int $comment_id, array $meta_input)
    {
        foreach ($meta_input as $key => $value) {
            static::update($comment_id, $key, $value);
        }

        return true;
    }

    /**
     * Delete a specific comment meta.
     *
     * @param int $comment_id
     * @param string $key
     * @param mixed $value
     *
     * @return bool
     */
    public static function delete(int $comment_id, string $key, $value = '')
    {
        return delete_comment_meta($comment_id, growfund_with_prefix($key), $value);
    }
}
