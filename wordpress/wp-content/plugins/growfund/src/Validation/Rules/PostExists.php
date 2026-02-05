<?php

namespace Growfund\Validation\Rules;

defined( 'ABSPATH' ) || exit;

use Growfund\Supports\PostMeta;

/**
 * Rule to ensure a a post exist with id and post type.
 *
 * @since 1.0.0
 */
class PostExists extends BaseRule
{
    /**
     * Check if the post exist and meet other rules like post_type, status, parent
     *
     * @return bool
     */
    public function validate_rule()
    {
        $post = get_post($this->value);

        if (empty($post)) {
            return false;
        }

        if (!empty($this->rule_value)) {
            $rules = explode(",", $this->rule_value);

            foreach ($rules as $rule) {
                if ($this->has_rule_check_failed($post, $rule)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Get the error message if the post does not exist.
     *
     * @return string
     */
    public function get_error_message()
    {
        /* translators: 1: field name, 2: field value */
        return sprintf(__('Resource with %1$s %2$s does not exist.', 'growfund'), str_replace(['_', '.'], ' ', $this->key), $this->value);
    }

    /**
     * Check if the post has a rule check failed
     * @param object $post
     * @param string $rule
     * @return bool
     */
    private function has_rule_check_failed($post, string $rule)
    {
        $rule_parts = explode('=', $rule, 2);

        $key = $rule_parts[0] ?? null;
        $value = $rule_parts[1] ?? null;

        switch ($key) {
            case 'post_type':
                return $post->post_type !== growfund_with_prefix($value);
            case 'status':
                $status = PostMeta::get($post->ID, 'status');
                return $status !== $value;
            case 'parent':
                $value = (int) $value ?? 0;
                return $post->post_parent !== $value;
            case 'author':
                $value = (int) $value ?? 0;
                return $post->post_author !== $value;
        }

        return false;
    }
}
