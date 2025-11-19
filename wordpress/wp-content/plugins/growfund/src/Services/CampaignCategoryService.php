<?php

namespace Growfund\Services;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\Status\TermStatus;
use Growfund\Sanitizer;
use Growfund\Supports\Arr;
use Growfund\Supports\MediaAttachment;
use Growfund\Supports\TermMeta;
use Growfund\Taxonomies\Category;
use Exception;
use Growfund\Http\Response;
use WP_Term;

/**
 * Campaign category service
 * @since 1.0.0
 */
class CampaignCategoryService
{
    /**
     * Make terms array into a hierarchical tree
     * 
     * @since 1.0.0
     * 
     * @param array $terms
     * @param int $parent_id
     * 
     * @return array
     */
    private function make_term_tree(array $terms, $parent_id = 0)
    {
        $tree = [];

        foreach ($terms as $term) {
            if ((int) $term['parent_id'] === (int) $parent_id) {
                $children = $this->make_term_tree($terms, $term['id']);

                if (!empty($children)) {
                    $term['children'] = $children;
                }
                $tree[] = $term;
            }
        }

        return $tree;
    }

    /**
     * Make a flat array from a hierarchical tree
     * 
     * @since 1.0.0
     * 
     * @param array $tree
     * @param int $level
     * 
     * @return array
     */
    private function flatten_term_tree(array $tree, int $level = 1)
    {
        $flattened = [];

        foreach ($tree as $node) {
            $item = $node;
            unset($item['children']);
            $item['level'] = $level;
            $flattened[] = $item;

            if (isset($node['children'])) {
                $flattened = array_merge(
                    $flattened,
                    $this->flatten_term_tree($node['children'], $level + 1)
                );
            }
        }

        return $flattened;
    }
    /**
     * Get all campaign categories
     * 
     * @param int|null $parent_id - 0 for top level categories, any positive non-zero
     *                             for sub categories, null for all categories including
     *                             sub categories
     * 
     * @return array<{id:int,name:string}>
     */
    protected function get($parent_id = null)
    {
        $args = [
            'taxonomy'   => Category::NAME,
            'hide_empty' => false,
            'fields'     => 'all_with_object_id',
            'pad_counts' => true,
            'orderby'    => 'id',
        ];

        if (!is_null($parent_id)) {
            $args['parent'] = $parent_id;
        }

        $categories = get_terms($args);

        if (is_wp_error($categories)) {
            throw new Exception(esc_html($categories->get_error_message()));
        }

        $response =  Arr::make($categories)->map(function (WP_Term $category) {
            $image = get_term_meta($category->term_id, growfund_with_prefix('image'), true);
            $is_default = get_term_meta($category->term_id, growfund_with_prefix('is_default'), true);
            return [
                'id'   => (string) $category->term_id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'parent_id' => (string) $category->parent,
                'image' => $image ? MediaAttachment::make($image) : null,
                'count' => $category->count ?? 0,
                'is_default' => isset($is_default) ? filter_var($is_default, FILTER_VALIDATE_BOOLEAN) : false,
                'level' => 1,
            ];
        })->toArray();

        if (is_null($parent_id)) {
            $response = $this->flatten_term_tree(
                $this->make_term_tree($response)
            );
        }

        return $response;
    }

    /**
     * Get top level categories
     * 
     * @return array<{id:int,name:string,slug:string,description:string,parent_id:int,image:array<{id:int,url:string,alt:string,title:string}>,count:int,level:int}>
     */
    public function get_top_level_categories()
    {
        return $this->get(0);
    }

    /**
     * Get sub categories
     * 
     * @param int $parent_id
     * 
     * @return array<{id:int,name:string,slug:string,description:string,parent_id:int,image:array<{id:int,url:string,alt:string,title:string}>,count:int,level:int}>
     */
    public function get_sub_categories(int $parent_id)
    {
        return $this->get($parent_id);
    }

    /**
     * Get all categories
     * 
     * @return array<{id:int,name:string,slug:string,description:string,parent_id:int,image:array<{id:int,url:string,alt:string,title:string}>,count:int,level:int}>
     */
    public function get_all()
    {
        return $this->get();
    }

    /**
     * Create campaign category
     * 
     * @param array{name:string,slug?:string,description?:string,parent_id?:int} $data
     * 
     * @return array{term_id:int,term_taxonomy_id:int}
     */
    public function create(array $data)
    {
        $name = $data['name'];
        $input = [
            'slug' => $data['slug'] ?? Sanitizer::apply_rule($name, Sanitizer::TITLE),
            'description' => $data['description'] ?? '',
            'parent' => $data['parent_id'] ?? 0,
        ];

        $result = wp_insert_term($name, Category::NAME, $input);

        if (is_wp_error($result)) {
            throw new Exception(esc_html($result->get_error_message()));
        }

        $image =  $data['image'] ?? null;

        TermMeta::update($result['term_id'], growfund_with_prefix('image'), $image);
        TermMeta::update($result['term_id'], 'status', $data['status'] ?? TermStatus::PUBLISHED);

        return $result;
    }

    /**
     * Update campaign category
     * 
     * @param int $id
     * @param array{name:string,slug?:string,description?:string,parent_id?:int} $data
     * 
     * @return bool
     */
    public function update(int $id, array $data)
    {
        if (isset($data['slug']) && $data['slug'] === '') {
            $data['slug'] = Sanitizer::apply_rule($data['name'], Sanitizer::TITLE);
        }

        $data['parent'] = $data['parent_id'] ?? 0;
        $result = wp_update_term($id, Category::NAME, $data);

        if (is_wp_error($result)) {
            throw new Exception(esc_html__('Category not found', 'growfund'));
        }

        $image = $data['image'] ?? null;
        TermMeta::update($id, 'image', $image);

        if (!empty($data['status'])) {
            TermMeta::update($id, 'status', $data['status']);
        }

        return !empty($result);
    }

    /**
     * Delete campaign category and subcategory
     * 
     * @param int $id
     * @return bool
     */
    public function delete(int $id, bool $is_permanent_delete = true)
    {
        $default_category_id = $this->get_default_category_id();

        if ($default_category_id === $id) {
            throw new Exception(esc_html__('Cannot delete default category.', 'growfund'));
        }

        $term = get_term($id, Category::NAME);

        if (!$term || is_wp_error($term)) {
            throw new Exception(esc_html__('Term not found.', 'growfund'), (int) Response::NOT_FOUND);
        }

        if ($is_permanent_delete) {
            $deleted = wp_delete_term($id, Category::NAME);

            if (is_wp_error($deleted)) {
                throw new Exception(esc_html__('Failed to delete term.', 'growfund'));
            }

            return true;
        }

        TermMeta::update($id, 'status', TermStatus::TRASHED);

        return true;
    }


    public function bulk_delete(array $ids)
    {
        $succeeded = [];
        $failed = [];

        foreach ($ids as $id) {
            $result = $this->delete($id);

            if ($result === false) {
                $failed[] = [
                    'id' => $id,
                    'message' => __('Category not found.', 'growfund'),
                ];
            } else {
                $succeeded[] = [
                    'id' => $id,
                    'message' => __('Category deleted successfully.', 'growfund'),
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
        $taxonomy = Category::NAME;

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

    /**
     * Get default category id
     * 
     * @return int
     */
    public function get_default_category_id()
    {
        $default_term = get_terms([
            'taxonomy'   => Category::NAME,
            'hide_empty' => false,
            'number'     => 1,
            'fields'     => 'ids',
            'meta_query' => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
                [
                    'key'   => growfund_with_prefix('is_default'),
                    'value' => 1,
                ],
            ]
        ]);

        return $default_term[0] ?? 0;
    }
}
