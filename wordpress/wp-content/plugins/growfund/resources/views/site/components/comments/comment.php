<?php
/**
 * @var Growfund\Views\Components\Comments\Comment $comment
 */

use Growfund\Supports\Date;
use Growfund\Views\Components\Comments\CommentBox;
use Growfund\Views\Components\Comments\CommentReply;
use Growfund\Views\Components\Form\Button;

defined('ABSPATH') || exit;

?>

<div class="growfund-comment-item">
    <div class="growfund-comment-avatar"><?php echo esc_html(ucfirst(substr($comment->comment->author_name ?? '', 0, 1))); ?></div>
    <div class="growfund-comment-details">
        <div class="growfund-comment-author-header">
            <div class="growfund-comment-author"><?php echo esc_html($comment->comment->author_name ?? ''); ?></div>
            <div class="growfund-comment-time"><?php echo esc_html(Date::human_readable_time_diff($comment->comment->created_at)); ?></div>
        </div>
        <p class="growfund-comment-text"><?php echo esc_html($comment->comment->content); ?></p>
        <div class="growfund-comment-reply-wrapper">
            <?php
            $growfund_reply_button = new Button();
            $growfund_reply_button->classname = 'growfund-comment-reply-btn';
            $growfund_reply_button->svg_icon = 'assets/site/icon/reply.svg';
            $growfund_reply_button->label = sprintf('%s reply', $comment->comment->replies->total); 
            growfund_render($growfund_reply_button);
            ?>
            <?php 
			$growfund_comment_box = new CommentBox();
            $growfund_comment_box->classname = 'growfund-comment-reply-input-box growfund-hidden';
            $growfund_comment_box->has_cancel_button = true;
            $growfund_comment_box->placeholder = __('Write a reply...', 'growfund');
			$growfund_comment_box->button_label = __("Reply", 'growfund');
            $growfund_comment_box->post_id = $comment->comment->post_id;
            $growfund_comment_box->parent_id = $comment->comment->id;
            $growfund_comment_box->comment_type = $comment->comment->comment_type;

			growfund_render($growfund_comment_box);
			?>
            <div class="growfund-comment-replies-thread-wrapper growfund-hidden" >
                <div class="growfund-comment-replies-thread" 
                    data-current-page="<?php echo esc_attr($comment->comment->replies->current_page ?? '0'); ?>" 
                    data-has-more="<?php echo esc_attr($comment->comment->replies->has_more ? 'true' : 'false'); ?>"
                    data-parent="<?php echo esc_attr($comment->comment->id); ?>"
                    data-comment-type="<?php echo esc_attr($comment->comment->comment_type); ?>"
                    data-post-id="<?php echo esc_attr($comment->comment->post_id); ?>"
                >
                <?php 
                if (!empty($comment->comment->replies->results)) {
                    foreach ($comment->comment->replies->results as $growfund_comment_reply) {
                        $growfund_reply_comment = new CommentReply();
                        $growfund_reply_comment->comment = $growfund_comment_reply;
                        growfund_render($growfund_reply_comment);
                    }
                }
                ?>
                </div>
                <div class="growfund-comment-reply-load-more <?php echo esc_attr($comment->comment->replies->has_more ? '' : 'growfund-hidden'); ?>">
                    <?php esc_html_e('Load more replies...', 'growfund'); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="growfund-v-line <?php echo $comment->comment->replies->total > 0 ? '' : 'growfund-hidden'; ?>"></div>
</div>
