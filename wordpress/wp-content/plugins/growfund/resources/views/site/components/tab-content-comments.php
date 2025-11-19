<?php

defined( 'ABSPATH' ) || exit;

/**
 * Comments Tab Content Component
 */

// Get the campaign ID from the template arguments
$campaign_id = $campaign_id ?? null;
$post_id = $campaign_id ?? get_the_ID(); // phpcs:ignore -- intentionally used

// Use pre-prepared data from controller
$comments = $comments ?? []; // phpcs:ignore -- intentionally used
$commentFormData = $comment_form_data ?? null;
$commentFormHtml = '';

// Use comment form HTML from controller if available
if ($commentFormData && !empty($commentFormData->comment_form_html)) {
    $commentFormHtml = $commentFormData->comment_form_html;
}

// If no comment form data is provided or comments are disabled, don't show anything
if (empty($commentFormHtml)) {
    $commentFormHtml = '';
}

?>

<?php if (!empty($comments) || !empty($commentFormHtml)) : ?>
    <div class="growfund-tab-content growfund-tab-content--comments" data-tab="comments">
        <div class="growfund-comments">
            <div class="growfund-comments__container">
                <?php
                try {
                    growfund_renderer()
                        ->render('site.components.comment', [
                            'showMainWrapper' => true,
                            'commentsList' => $comments,
                            'comment_type' => 'comment',
                            'post_id' => $post_id,
                            'commentFormHtml' => $commentFormHtml
                        ]);
                } catch (Exception $error) {
                    growfund_renderer()->render('site.components.comments-fallback', [
                        'state' => 'unavailable'
                    ]);
                }
                ?>

                <div class="growfund-card growfund-comments__sidebar">
                    <div class="growfund-comments__support">
                        <p class="growfund-comments__support-text"><?php esc_html_e('This is your space to offer support and feedback. Remember to be constructive—there\'s a human behind this project.', 'growfund'); ?></p>
                    </div>

                    <a href="#faq" class="growfund-faq-prompt">
                        <div class="growfund-faq-prompt__text"><?php esc_html_e('Have a question for the creator?', 'growfund'); ?></div>
                        <div class="growfund-faq-prompt__action"><?php esc_html_e('Check this campaign\'s FAQ →', 'growfund'); ?></div>
                    </a>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>