<?php

namespace Growfund\Views\Components\Comments;

use Growfund\DTO\Comment\CommentDTO;
use Growfund\View;

defined( 'ABSPATH' ) || exit;

class CommentCollection extends View {

    /** @var CommentDTO */
    public $comments;

    /** @var bool */
    public $is_reply;


    protected function get_template_dir() {
        return 'site/components/comments';
    }

    public $casts = [
        'comments.*' => CommentDTO::class
    ];
}
