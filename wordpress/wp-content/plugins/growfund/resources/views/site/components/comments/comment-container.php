<?php
/**
 * @var Growfund\Views\Components\Comments\CommentContainer $comment_container
 */

use Growfund\Views\Components\Comments\CommentBox;

defined('ABSPATH') || exit;

?>

<div 
    class="growfund-comment-container"
    <?php echo $comment_container->id ? 'id="' . esc_attr($comment_container->id) . '"' : ''; ?>
>
    <?php 
    $growfund_comment_box = new CommentBox();
    $growfund_comment_box->placeholder = __('Share your thoughts, ask questions or show your support...', 'growfund');
    $growfund_comment_box->button_label = __("Post Comment", 'growfund');
    $growfund_comment_box->post_id = $comment_container->post_id;
    $growfund_comment_box->comment_type = $comment_container->comment_type;

    growfund_render($growfund_comment_box);
    ?>

    <div class="growfund-comment-content-list-wrapper">
        <div 
            class="growfund-comment-content-list" 
            data-post-id="<?php echo esc_attr($comment_container->post_id); ?>"
            data-comment-type="<?php echo esc_attr($comment_container->comment_type); ?>"
            data-current-page="0" 
            data-has-more="true"
        ></div>
        <div class="growfund-comment-load-more growfund-hidden"><?php esc_html_e('Load more comments...', 'growfund'); ?></div>
       
    </div>
</div>
