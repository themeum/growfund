<?php
/**
 * @var Growfund\Views\Components\Campaign\Tabs\CommentContent $comment_content
 */

defined('ABSPATH') || exit;

use Growfund\PostTypes\Campaign;
use Growfund\Views\Components\Comments\CommentContainer;

?>

<div class="growfund-comment-tab-content-container">
    <div class="growfund-comment-tab-content-main">
        <?php 
        $growfund_comment_container = new CommentContainer();
        $growfund_comment_container->post_id = $comment_content->campaign_id;
        $growfund_comment_container->id = "growfund_campaign_comment_container";
        $growfund_comment_container->comment_type = Campaign::COMMENT_TYPE;

        growfund_render($growfund_comment_container);
        ?>
    </div>

    <?php if ($comment_content->has_faqs) : ?>
        <div class="growfund-comment-tab-content-sidebar">
        
            <p class="growfund-comment-tab-content-info-text">
                <?php esc_html_e("This is your space to offer support and feedback. Remember to be constructive — there's a human behind this project.", 'growfund'); ?>
            </p>
            <div class="growfund-comment-tab-content-faq-banner">
            
                <a href="<?php echo esc_url(growfund_campaign_url($comment_content->campaign_id)); ?>#faq" class="growfund-comment-tab-content-faq-link">
                    <span class="growfund-comment-tab-content-faq-ques"><?php esc_html_e('Have a question for the creator?', 'growfund'); ?></span>
                    <span class="growfund-comment-tab-content-faq-ans"><?php esc_html_e( "Check this campaign's FAQ→", 'growfund' ); ?></span>
                    </a>
            </div>
    
        </div>
    <?php endif; ?>
</div>
