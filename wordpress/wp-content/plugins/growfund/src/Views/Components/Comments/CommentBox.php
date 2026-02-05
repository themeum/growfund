<?php

namespace Growfund\Views\Components\Comments;

use Growfund\View;

defined( 'ABSPATH' ) || exit;

class CommentBox extends View {

    /** @var string */
    public $classname;

    /** @var bool */
    public $has_cancel_button = false;

    /** @var string */
    public $placeholder = '';

    /** @var string */
    public $button_label;

    /** @var string */
    public $post_id;

    /** @var string */
    public $parent_id;

    /** @var string */
    public $comment_type;

    
    protected function get_template_dir() {
        return 'site/components/comments';
    }

    protected function enqueue_styles()
    {
        $main_styles_url = GROWFUND_DIR_URL . 'resources/assets/site/styles/components/comments/comment-box.css';

        wp_enqueue_style(
            'growfund-comment-box-styles',
            $main_styles_url,
            ['growfund-main-styles'],
            GROWFUND_VERSION
        );
    }

    protected function enqueue_scripts()
    {
        $script_url = GROWFUND_DIR_URL . 'resources/assets/site/scripts/components/comments/comment-box.js';
        wp_enqueue_script('growfund-comment-box-script', $script_url, ['growfund-core'], GROWFUND_VERSION, true);
    }
}
