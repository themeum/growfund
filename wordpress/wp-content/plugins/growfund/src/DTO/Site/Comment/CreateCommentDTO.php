<?php

namespace Growfund\DTO\Site\Comment;

defined( 'ABSPATH' ) || exit;

use Growfund\DTO\DTO;
use Growfund\Sanitizer;

/**
 * DTO for creating new comments
 */
class CreateCommentDTO extends DTO
{
    /** @var string */
    public $post_id;

    /** @var string */
    public $comment_content;

    /** @var string */
    public $parent_id;

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
            'post_id' => 'required|integer|min:1',
            'comment_content' => 'required|string|min:1',
            'parent_id' => 'integer|min:0',
            'comment_type' => 'string|in:comment,update_comment'
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
            'comment_content' => Sanitizer::TEXTAREA,
            'parent_id' => Sanitizer::INT,
            'comment_type' => Sanitizer::TEXT,
        ];
    }
}
