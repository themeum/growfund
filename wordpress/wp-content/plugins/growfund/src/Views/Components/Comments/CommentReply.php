<?php

namespace Growfund\Views\Components\Comments;

use Growfund\DTO\Comment\CommentDTO;
use Growfund\View;

defined( 'ABSPATH' ) || exit;

class CommentReply extends View {
    /** @var CommentDTO */
    public $comment;


    protected function get_template_dir() {
        return 'site/components/comments';
    }

    protected function enqueue_styles()
    {
        $main_styles_url = GROWFUND_DIR_URL . 'resources/assets/site/styles/components/comments/comment-reply.css';

        wp_enqueue_style(
            'growfund-comment-reply-styles',
            $main_styles_url,
            ['growfund-main-styles'],
            GROWFUND_VERSION
        );
    }

    public $casts = [
        'comment' => CommentDTO::class
    ];
}
