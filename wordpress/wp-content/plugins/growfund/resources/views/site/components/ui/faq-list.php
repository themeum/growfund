<?php

/** @var Growfund\Views\Components\UI\FaqList $faq_list */
defined( 'ABSPATH' ) || exit;

?>

<div class="growfund-faq-list">
    <?php foreach ($faq_list->faqs as $growfund_index => $growfund_faq) : ?>
        <div 
            class="growfund-faq-list-item 
            <?php 
                echo !is_null($faq_list->default_open_index) && $growfund_index === $faq_list->default_open_index 
                    ? esc_attr('active') 
                    : ''; 
            ?>
            "
        >
            <h3 class="growfund-faq-list-question-wrapper">
                <button type="button" class="growfund-faq-list-question">
                    <span class="growfund-faq-item-icon" aria-hidden="true">
                        <span class="growfund-faq-item-icon-line horizontal"></span>
                        <span class="growfund-faq-item-icon-line vertical"></span>
                    </span>
                    <span class="growfund-faq-item-question-text"><?php echo esc_html($growfund_faq['question'] ?? ''); ?></span>
                </button>
            </h3>
            <div class="growfund-faq-item-answer-wrapper">
                <div class="growfund-faq-item-answer">
                    <p><?php echo esc_html($growfund_faq['answer'] ?? ''); ?></p>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

