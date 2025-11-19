<?php

defined( 'ABSPATH' ) || exit;

/**
 * Comment Component (Simplified)
 * Expects the following variables:
 * - array  $commentsList (array of CommentCardItemDTO)
 * - bool   $showMainWrapper
 * - string $comment_type
 * - int    $post_id
 * - bool   $isAjaxResponse
 * - bool   $nested
 * - object $comment (CommentCardItemDTO, for single comment render)
 * - array  $replyList (array of CommentCardItemDTO, for thread)
 * - bool   $isThread
 */





// Component to render comment actions
if (!function_exists('render_comment_actions')) {
    function render_comment_actions($replies, $comment_id = null, $can_reply = true, $is_nested = false)
    {
        $reply_button = '';
        if ($can_reply && $comment_id && !$is_nested) {
            $reply_text = $replies > 0
                ? /* translators: %d: number of replies */
                sprintf(_n('%d reply', '%d replies', $replies, 'growfund'), $replies)
                : __('Reply', 'growfund');
            $reply_button = sprintf(
                /* translators: 1: comment id, 2: reply icon, 3: reply text */
                '<div class="growfund-comment__action growfund-comment__reply-btn" data-comment-id="%1$d">
                    %2$s
                    <span class="growfund-comment__action-text">%3$s</span>
                </div>',
                $comment_id,
                growfund_renderer()->get_html('site.components.icon', [
					'name' => 'reply',
					'size' => 'sm'
				]),
                $reply_text
            );
        }
        return sprintf(
            '<div class="growfund-comment__actions">%s</div>',
            $reply_button
        );
    }
}

// Component to render a single comment
if (!function_exists('render_single_comment')) {
    function render_single_comment($comment, $nested = false)
    {
        $avatar = $comment->avatar ?? '';
        $author = $comment->author ?? 'Anonymous';
        $time = $comment->time ?? '';
        $text = $comment->text ?? '';
        $commentReplies = $comment->replies_count ?? (is_array($comment->replies) ? count($comment->replies) : 0);
        $comment_id = $comment->id ?? null;
        $can_reply = $comment->can_reply ?? true;
        $commentClass = 'growfund-comment' . ($nested ? ' growfund-comment--nested' : '');
        // Build avatar or placeholder
        $avatarHtml = '';
        if (!empty($avatar)) {
            $avatarHtml = growfund_renderer()->get_html('site.components.image', [
                'src' => $avatar,
                'alt' => $author,
                'attributes' => ['class' => 'growfund-comment__avatar']
            ]);
        } else {
            $firstLetter = function_exists('mb_substr') ? mb_substr($author, 0, 1) : substr($author, 0, 1);
            $avatarHtml = '<div class="growfund-comment__avatar-placeholder">' . esc_html(strtoupper($firstLetter)) . '</div>';
        }

        // Check if comment is awaiting approval
        $approvalMessage = '';
        if (isset($comment->is_awaiting_approval) && $comment->is_awaiting_approval) {
            $approvalMessage = '<div class="growfund-comment__approval-notice">' . esc_html__('Awaiting Approval', 'growfund') . '</div>';
        }

        return sprintf(
            '
            <div class="%1$s" data-comment-id="%8$s">
                <div class="growfund-comment__avatar-container">
                    %2$s
                </div>
                <div class="growfund-comment__content">
                    <div class="growfund-comment__header">
                        <div class="growfund-comment__author-line">
                            <span class="growfund-comment__author">%3$s</span>
                        </div>
                        <span class="growfund-comment__time">%4$s</span>
                    </div>
                    %5$s
                    <p class="growfund-comment__text">%6$s</p>
                    %7$s
                </div>
            </div>',
            esc_attr($commentClass),
            $avatarHtml,
            esc_html($author),
            esc_html($time),
            $approvalMessage,
            esc_html($text),
            render_comment_actions($commentReplies, $comment_id, $can_reply, $nested),
            $comment_id
        );
    }
}

// Main rendering logic
if (!empty($showMainWrapper)) : ?>
    <div class="growfund-comments__main">
        <?php if (!empty($commentFormHtml)) : ?>
            <div class="growfund-card growfund-comments__form-wrapper" id="comment-form-wrapper-<?php echo esc_attr($comment_type); ?>">
                <?php echo $commentFormHtml; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- already escaped ?>
            </div>
        <?php endif; ?>
        <div class="growfund-card growfund-comments__list" data-comment-type="<?php echo esc_attr($comment_type); ?>" data-post-id="<?php echo esc_attr($post_id); ?>">
            <?php if (!empty($commentsList)) : ?>
                <?php foreach ($commentsList as $comment_data) : ?>
                    <div class="growfund-comment-thread<?php echo !empty($comment_data->replies) ? ' has-replies' : ''; ?>" data-comment-id="<?php echo esc_attr($comment_data->id); ?>">
                        <?php
                        echo render_single_comment($comment_data); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- already escaped
                        if (!empty($comment_data->replies)) :
							?>
                            <div class="growfund-replies">
                                <?php foreach ($comment_data->replies as $reply) : ?>
                                    <?php echo render_single_comment($reply, true); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- already escaped ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <div class="growfund-no-comments">
                    <h3><?php esc_html_e('No comments yet', 'growfund'); ?></h3>
                    <?php if (!empty($commentFormHtml)) : ?>
                        <p><?php esc_html_e('Be the first to comment on this campaign.', 'growfund'); ?></p>
                    <?php else : ?>
                        <p><?php esc_html_e('Comments are currently disabled for this campaign.', 'growfund'); ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
    elseif (!empty($isThread)) :
		?>
        <div class="growfund-comment-thread<?php echo !empty($replyList) ? ' has-replies' : ''; ?>">
				<?php
				echo render_single_comment($comment); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- already escaped
				if (!empty($replyList)) :
					?>
                <div class="growfund-replies">
						<?php foreach ($replyList as $reply) : ?>
							<?php echo render_single_comment($reply, true); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- already escaped ?>
                    <?php endforeach; ?>
                </div>
				<?php endif; ?>
        </div>
		<?php
    elseif (!empty($isAjaxResponse)) :
		if (!empty($nested)) {
			echo render_single_comment($comment, $nested); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- already escaped
		} else {
			$comment_id = is_object($comment) ? $comment->id : ($comment['id'] ?? null);
			echo '<div class="growfund-comment-thread" data-comment-id="' . esc_attr($comment_id) . '">';
			echo render_single_comment($comment, $nested); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- already escaped
			echo '</div>';
		}
		else :
            echo render_single_comment($comment, $nested); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- already escaped
endif;
