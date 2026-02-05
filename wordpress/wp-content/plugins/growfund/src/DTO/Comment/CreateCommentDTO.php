<?php

namespace Growfund\DTO\Comment;

defined( 'ABSPATH' ) || exit;

use Growfund\DTO\DTO;
use Growfund\PostTypes\Campaign;
use Growfund\PostTypes\CampaignPost;
use Growfund\Sanitizer;

/**
 * DTO for creating new comments
 */
class CreateCommentDTO extends DTO
{
    /** @var string */
    public $post_id;

    /** @var string */
    public $content;

    /** @var string */
    public $parent_id = 0;

    /** @var string */
    public $comment_type;

    /**
     * Return validation rules
     *
     * @return array
     */
    public static function validation_rules(): array
    {
        return [
            'post_id' => [
                'required',
                'integer',
                function ($value, $key, $data) {
                    $comment_type = $data['comment_type'] ?? '';
                    $post = get_post($value);
                    if ($comment_type === CampaignPost::COMMENT_TYPE) {
                        if (!$post || $post->post_type !== CampaignPost::NAME) {
                            /* translators: %s: field name */
                            return sprintf(__('The %s field is not exists.', 'growfund'), $key);
                        }
                    } elseif ($comment_type === Campaign::COMMENT_TYPE) {
                        if (!$post || $post->post_type !== Campaign::NAME) {
                            /* translators: %s: field name */
                            return sprintf(__('The %s field is not exists.', 'growfund'), $key);
                        }
                    }

                    return true;
                },
            ],
            'content' => 'required|string|max:1000',
            'parent_id' => 'integer|min:0',
            'comment_type' => 'string|in:' . Campaign::COMMENT_TYPE . ',' . CampaignPost::COMMENT_TYPE
        ];
    }

    /**
     * Return sanitization rules
     *
     * @return array
     */
    public static function sanitization_rules(): array
    {
        return [
            'post_id' => Sanitizer::INT,
            'content' => Sanitizer::TEXTAREA,
            'parent_id' => Sanitizer::INT,
            'comment_type' => Sanitizer::TEXT,
        ];
    }
}
