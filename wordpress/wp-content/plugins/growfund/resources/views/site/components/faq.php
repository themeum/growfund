<?php

defined( 'ABSPATH' ) || exit;

/**
 * FAQ Component
 * Reusable FAQ accordion component with smooth animations and accessibility
 */

// Get FAQ items from campaign data
$faq_items = $faqs ?? [];

// Generate unique IDs for accessibility
$unique_id = uniqid('faq_');
?>

<div class="growfund-faq-content" data-component="faq-accordion">
    <?php if (!empty($faq_items)) : ?>
        <div class="growfund-faq-list" role="region" aria-label="<?php echo esc_attr__('Frequently Asked Questions', 'growfund'); ?>">
            <?php
            foreach ($faq_items as $index => $item) :
                $question_id = $unique_id . '_question_' . $index;
                $panel_id = $unique_id . '_panel_' . $index;
				?>
                <div class="growfund-faq-item <?php echo !empty($item['active']) ? 'active' : ''; ?>" data-faq-item>
                    <h3 class="growfund-faq-question-wrapper">
                        <button
                            class="growfund-faq-question"
                            type="button"
                            aria-expanded="<?php echo !empty($item['active']) ? 'true' : 'false'; ?>"
                            aria-controls="<?php echo esc_attr($panel_id); ?>"
                            id="<?php echo esc_attr($question_id); ?>"
                            data-faq-trigger>
                            <span class="growfund-faq-icon" aria-hidden="true">
                                <span class="growfund-faq-icon-line growfund-faq-icon-line--horizontal"></span>
                                <span class="growfund-faq-icon-line growfund-faq-icon-line--vertical"></span>
                            </span>
                            <span class="growfund-faq-question-text"><?php echo esc_html($item['question']); ?></span>
                        </button>
                    </h3>
                    <div
                        class="growfund-faq-answer-wrapper"
                        id="<?php echo esc_attr($panel_id); ?>"
                        role="region"
                        aria-labelledby="<?php echo esc_attr($question_id); ?>"
                        data-faq-panel>
                        <div class="growfund-faq-answer-content">
                            <p><?php echo esc_html($item['answer']); ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else : ?>
        <div class="growfund-faq-empty">
            <p class="growfund-faq-empty-message"><?php esc_html_e('No frequently asked questions available for this campaign.', 'growfund'); ?></p>
        </div>
    <?php endif; ?>
</div>