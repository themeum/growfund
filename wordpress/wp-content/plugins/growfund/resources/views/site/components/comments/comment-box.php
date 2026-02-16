<?php
/**
 * @var Growfund\Views\Components\Comments\CommentBox $comment_box
 */

defined('ABSPATH') || exit;

use Growfund\Core\AppSettings;
use Growfund\Views\Components\Form\Button;

$growfund_is_allowed_to_comment = true;

if (growfund_user()->is_backer() || growfund_user()->is_donor()) {
    $growfund_is_allowed_to_comment = growfund_settings(AppSettings::PERMISSIONS)->allow_contributor_comments();
}
?>

<?php if ($growfund_is_allowed_to_comment) : ?>
    <div class="growfund-comment-input-box <?php echo esc_attr($comment_box->classname ? $comment_box->classname : ''); ?>">
        <input type="hidden" name="post_id" value="<?php echo esc_attr($comment_box->post_id); ?>" />
        <input type="hidden" name="comment_type" value="<?php echo esc_attr($comment_box->comment_type); ?>" />
        <?php if ($comment_box->parent_id) : ?>
            <input type="hidden" name="parent_id" value="<?php echo esc_attr($comment_box->parent_id); ?>" />
        <?php endif; ?>

        <textarea class="growfund-comment-textarea" name="content" placeholder="<?php echo esc_html($comment_box->placeholder); ?>"></textarea>
        
        <div class="growfund-comment-input-footer">
            <span class="growfund-comment-input-char-count"><span>0</span> / 1000</span>
            <div class="growfund-comment-input-btn-wrapper">
                <?php
                if ($comment_box->has_cancel_button) {
                    $growfund_cancel_button = new Button();
                    $growfund_cancel_button->classname = 'growfund-comment-cancel-btn';
                    $growfund_cancel_button->label = __('Cancel', 'growfund'); 
                    growfund_render($growfund_cancel_button);
                }

                $growfund_post_button = new Button();
                $growfund_post_button->classname = 'growfund-comment-post-btn growfund-branding-btn';
                $growfund_post_button->label = $comment_box->button_label; 
                growfund_render($growfund_post_button);
                ?>
            </div>
        </div>
    </div>
<?php endif; ?>
