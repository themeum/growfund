<?php

namespace Growfund\Views\Components\Comments;

use Growfund\View;

defined( 'ABSPATH' ) || exit;

class CommentContainer extends View {

    /** @var string */
    public $post_id;

    /** @var string */
    public $comment_type;

    /** @var string */
    public $id;


    protected function get_template_dir() {
        return 'site/components/comments';
    }

    protected function enqueue_styles()
    {
        $main_styles_url = GROWFUND_DIR_URL . 'resources/assets/site/styles/components/comments/comment-container.css';

        wp_enqueue_style(
            'growfund-comment-container-styles',
            $main_styles_url,
            ['growfund-main-styles'],
            GROWFUND_VERSION
        );

        $comment_styles_url = GROWFUND_DIR_URL . 'resources/assets/site/styles/components/comments/comment.css';

        wp_enqueue_style(
            'growfund-comment-styles',
            $comment_styles_url,
            ['growfund-comment-container-styles'],
            GROWFUND_VERSION
        );

        $reply_styles_url = GROWFUND_DIR_URL . 'resources/assets/site/styles/components/comments/comment-reply.css';

        wp_enqueue_style(
            'growfund-comment-reply-styles',
            $reply_styles_url,
            ['growfund-comment-container-styles'],
            GROWFUND_VERSION
        );
    }

	protected function enqueue_scripts()
    {
        $script_url = GROWFUND_DIR_URL . 'resources/assets/site/scripts/components/comments/comment.js';
        wp_enqueue_script('growfund-comment-script', $script_url, ['growfund-core'], GROWFUND_VERSION, true);
    }
}
