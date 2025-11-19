<?php

namespace Growfund\Services;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\Status\TermStatus;
use Growfund\Sanitizer;
use Growfund\Supports\Arr;
use Growfund\Supports\TermMeta;
use Growfund\Taxonomies\Tag;
use Exception;
use Growfund\Http\Response;
use WP_Term;

/**
 * Campaign tag service
 * @since 1.0.0
 */
class CampaignTagService
{
    /**
     * Get all campaign tags
     * @return array<{id:int,name:string,slug:string,description:string}>
     */
    public function get_all()
    {
        $tag_terms = get_terms(array(
            'taxonomy'   => Tag::NAME,
            'hide_empty' => false,
            'fields'     => 'all_with_object_id',
            'orderby'    => 'id',
        ));

        if (is_wp_error($tag_terms)) {
            throw new Exception(esc_html($tag_terms->get_error_message()));
        }

        return Arr::make($tag_terms)->map(function (WP_Term $tag_term) {
            return [
                'id'   => (string) $tag_term->term_id,
                'name' => $tag_term->name,
                'slug' => $tag_term->slug,
                'description' => $tag_term->description,
                'count' => (int) $tag_term->count,
            ];
        })->toArray();
    }

    /**
     * Create campaign tag
     * 
     * @param array{name:string,slug?:string,description?:string} $data
     * @return array{term_id:int,term_taxonomy_id:int}
     */
    public function create(array $data)
    {
        $name = $data['name'];
        $meta_input = [
            'slug' => $data['slug'] ?? Sanitizer::apply_rule($name, Sanitizer::TITLE),
            'description' => $data['description'] ?? '',
        ];

        $result = wp_insert_term($name, Tag::NAME, $meta_input);

        if (is_wp_error($result)) {
            throw new Exception(esc_html($result->get_error_message()));
        }

        return $result;
    }

    /**
     * Update campaign tag
     * 
     * @param int $id
     * @param array{name:string,slug?:string,description?:string} $data 
     *
     * @return bool
     */
    public function update(int $id, array $data)
    {
        if (isset($data['slug']) && $data['slug'] === '') {
            $data['slug'] = Sanitizer::apply_rule($data['name'], Sanitizer::TITLE);
        }

        $result = wp_update_term($id, Tag::NAME, $data);

        if (is_wp_error($result)) {
            throw new Exception(esc_html__('Tag not found', 'growfund'));
        }

        return !empty($result);
    }

    /**
     * Delete campaign tags
     * 
     * @param int $id
     * @return bool
     */
    public function delete(int $id, bool $is_permanent_delete = true)
    {
        $term = get_term($id, Tag::NAME);

        if (!$term || is_wp_error($term)) {
            throw new Exception(esc_html__('Tag not found.', 'growfund'), (int) Response::NOT_FOUND);
        }

        if ($is_permanent_delete) {
            $deleted = wp_delete_term($id, Tag::NAME);

            if (is_wp_error($deleted)) {
                throw new Exception(esc_html__('Failed to delete tag.', 'growfund'));
            }

            return true;
        }

        TermMeta::update($id, 'status', TermStatus::TRASHED);

        return true;
    }

    /**
     * Bulk delete campaign tags.
     * If there is any error while deleting one term then we will skip it.
     * Finally returns true if all the terms are deleted, false otherwise.
     *
     * @since 1.0.0
     *
     * @param array<int> $ids
     * @return array
     */
    public function bulk_delete(array $ids)
    {
        $succeeded = [];
        $failed = [];

        foreach ($ids as $id) {
            $result = $this->delete($id);

            if ($result === false) {
                $failed[] = [
                    'id' => $id,
                    'message' => __('Tag not found.', 'growfund'),
                ];
            } else {
                $succeeded[] = [
                    'id' => $id,
                    'message' => __('Tag deleted successfully.', 'growfund'),
                ];
            }
        }

        return [
            'succeeded' => $succeeded,
            'failed' => $failed,
        ];
    }

    public function empty_trash()
    {
        $taxonomy = Tag::NAME;

        $terms = get_terms([
            'taxonomy'   => $taxonomy,
            'hide_empty' => false,
            'meta_key'   => growfund_with_prefix('status'), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
            'meta_value' => TermStatus::TRASHED, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
            'fields'     => 'ids',
            'number'     => -1,
        ]);

        if (is_wp_error($terms) || empty($terms)) {
            return false;
        }

        $succeeded = [];
        $failed    = [];

        foreach ($terms as $term_id) {
            $deleted = wp_delete_term($term_id, $taxonomy);

            if ($deleted && !is_wp_error($deleted)) {
                $succeeded[] = $term_id;
            } else {
                $failed[] = $term_id;
            }
        }

        return count($succeeded) > 0;
    }
}
