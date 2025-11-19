<?php

defined( 'ABSPATH' ) || exit;

/**
 * Comment Form Component
 * 
 * This component renders a comment form for posts, updates, or replies.
 * 
 * @param int $comment_post_id The ID of the post/update to comment on
 * @param int $parent_id The ID of the parent comment (0 for top-level comments)
 * @param string $placeholder Placeholder text for the comment input
 * @param string $button_text Text for the submit button
 * @param bool $is_reply Whether this is a reply form
 * @param string $reply_to_author Name of the author being replied to
 * @param string $comment_type Type of comment ('comment', 'update_comment', etc.)
 * @param string $current_user_name Current user's display name
 * @param string $current_user_avatar Current user's avatar URL
 * @param bool $is_logged_in Whether the user is logged in
 */
?>
<div class="growfund-comment-form<?php echo $is_reply ? ' growfund-comment-form--reply' : ''; ?>"
    data-post-id="<?php echo esc_attr($comment_post_id); ?>"
    data-parent-id="<?php echo esc_attr($parent_id); ?>"
    data-comment-type="<?php echo esc_attr($comment_type); ?>">

    <?php if ($is_logged_in) : ?>

        <div class="growfund-comment-form__body">
            <div class="growfund-comment-form__input-wrapper">
                <textarea
                    class="growfund-comment-form__textarea"
                    placeholder="<?php echo esc_attr($placeholder); ?>"
                    rows="3"
                    maxlength="1000"></textarea>
            </div>
        </div>

        <div class="growfund-comment-form__footer">
            <div class="growfund-comment-form__char-count">
                <span class="growfund-comment-form__char-current">0</span>
                <span class="growfund-comment-form__char-separator">/</span>
                <span class="growfund-comment-form__char-max">1000</span>
            </div>

            <div class="growfund-comment-form__actions<?php echo $is_reply ? ' growfund-comment-form__actions--two-buttons' : ' growfund-comment-form__actions--one-button'; ?>">
                <?php if ($is_reply) : ?>
                    <button type="button" class="growfund-comment-form__cancel-btn">
                        <?php esc_html_e('Cancel', 'growfund'); ?>
                    </button>
                <?php endif; ?>
                <button type="button" class="growfund-comment-form__submit-btn growfund-btn growfund-btn--primary" disabled>
                    <?php echo esc_html($button_text); ?>
                </button>
            </div>
        </div>

        <div class="growfund-comment-form__error" style="display: none;">
            <p class="growfund-comment-form__error-message"></p>
        </div>

    <?php else : ?>
        <div class="growfund-comment-form__login-prompt">
            <p><?php esc_html_e('You must be logged in to comment.', 'growfund'); ?></p>
            <a href="<?php echo esc_url(growfund_login_url(get_permalink())); ?>" class="growfund-btn growfund-btn--primary">
                <?php esc_html_e('Login', 'growfund'); ?>
            </a>
        </div>
    <?php endif; ?>
</div>