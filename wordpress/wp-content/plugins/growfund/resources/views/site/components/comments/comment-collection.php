<?php
/**
 * @var Growfund\Views\Components\Comments\CommentCollection $comment_collection
 */

defined('ABSPATH') || exit;


use Growfund\Views\Components\Comments\Comment;
use Growfund\Views\Components\Comments\CommentReply;
use Growfund\Views\Components\Comments\EmptyComment;

if (!empty($comment_collection->comments)) {

    foreach ($comment_collection->comments as $growfund_comment) {

        if (!empty($comment_collection->is_reply)) {
            $growfund_comment_reply = new CommentReply();
            $growfund_comment_reply->comment = $growfund_comment;

            growfund_render($growfund_comment_reply);
            continue;
        }

        $growfund_comment_component = new Comment();
        $growfund_comment_component->comment = $growfund_comment;

        growfund_render( $growfund_comment_component );
    }

} elseif (empty($comment_collection->comments) && empty($comment_collection->is_reply)) {
    $growfund_empty_comment = new EmptyComment();
    growfund_render( $growfund_empty_comment );
}
