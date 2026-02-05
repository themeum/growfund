<?php
/**
 * @var Growfund\Views\Components\Comments\CommentReply $comment_reply
 */

defined('ABSPATH') || exit;

use Growfund\Supports\Date;

?>

<div class="growfund-comment-item growfund-is-reply">
    <div class="growfund-comment-avatar"><?php echo esc_html(ucfirst(substr($comment_reply->comment->author_name ?? '', 0, 1))); ?></div>
    <div class="growfund-comment-details">
        <div class="growfund-comment-author-header">
            <span class="growfund-comment-author"><?php echo esc_html($comment_reply->comment->author_name ?? ''); ?></span>
            <span class="growfund-comment-time"><?php echo esc_html(Date::human_readable_time_diff($comment_reply->comment->created_at)); ?></span>
        </div>
        <p class="growfund-comment-text"><?php echo esc_html($comment_reply->comment->content); ?></p>
    </div>
    <div class="growfund-reply-hook"></div>
</div>
