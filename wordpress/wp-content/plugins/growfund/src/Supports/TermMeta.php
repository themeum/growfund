<?php

namespace Growfund\Supports;

defined( 'ABSPATH' ) || exit;

/**
 * Class to handle the term meta
 *
 * Provides static methods for getting, setting, updating, and deleting term meta.
 *
 * @since 1.0.0
 */
class TermMeta
{
    /**
     * Retrieve term meta by ID and key.
     *
     * @param int $term_id
     * @param string $key
     * @param bool $single
     *
     * @return mixed
     */
    public static function get(int $term_id, string $key = '', bool $single = true)
    {
        if (!empty($key)) {
            return maybe_unserialize(get_term_meta($term_id, growfund_with_prefix($key), $single));
        }

        $metadata = get_term_meta($term_id, '', $single);

        $updated_metadata = [];

        foreach ($metadata as $raw_key => $value) {
            $key = growfund_without_prefix($raw_key);
            $updated_metadata[$key] = $single ? maybe_unserialize($value[0]) : maybe_unserialize($value);
        }

        return $updated_metadata;
    }

    /**
     * Get all term meta by ID.
     *
     * @param int $term_id
     * @param bool $single
     *
     * @return array
     */
    public static function get_all(int $term_id, $single = true)
    {
        return static::get($term_id, '', $single);
    }

    /**
     * Add a single term meta.
     *
     * @param int $term_id
     * @param string $key
     * @param mixed $value
     * @param bool $unique
     *
     * @return bool|int
     */
    public static function add(int $term_id, string $key, $value, $unique = false)
    {
        return add_term_meta($term_id, growfund_with_prefix($key), $value, $unique);
    }

    /**
     * Add multiple term meta at once.
     *
     * @param int $term_id
     * @param array $meta_input
     *
     * @return bool
     */
    public static function add_many(int $term_id, array $meta_input)
    {
        foreach ($meta_input as $key => $value) {
            static::add($term_id, $key, $value);
        }

        return true;
    }

    /**
     * Update a single term meta.
     *
     * @param int $term_id
     * @param string $key
     * @param mixed $value
     * @param mixed $prev_value
     *
     * @return bool|int
     */
    public static function update(int $term_id, string $key, $value, $prev_value = '')
    {
        return update_term_meta($term_id, growfund_with_prefix($key), $value, $prev_value);
    }

    /**
     * Update multiple term meta at once.
     *
     * @param int $term_id
     * @param array $meta_input
     *
     * @return bool
     */
    public static function update_many(int $term_id, array $meta_input)
    {
        foreach ($meta_input as $key => $value) {
            static::update($term_id, $key, $value);
        }

        return true;
    }

    /**
     * Delete a term meta.
     *
     * @param int $term_id
     * @param string $key
     * @param mixed $value
     *
     * @return bool
     */
    public static function delete(int $term_id, string $key, $value = '')
    {
        return delete_term_meta($term_id, growfund_with_prefix($key), $value);
    }
}
