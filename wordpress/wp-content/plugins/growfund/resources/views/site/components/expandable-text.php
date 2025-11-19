<?php

defined( 'ABSPATH' ) || exit;

/**
 * Expandable Text Component
 * 
 * A reusable component for displaying text with expand/collapse functionality.
 * 
 * @param string $text The text to display
 * @param int $max_lines Maximum number of lines to show before truncating (default: 2)
 * @param string $read_more_text Text for the "Read more" link (default: "Read more")
 * @param string $read_less_text Text for the "Read less" link (default: "Read less")
 * @param string $container_class Additional CSS classes for the container
 * @param string $text_class Additional CSS classes for the text element
 */
?>

<div class="growfund-expandable-text <?php echo esc_attr($container_class ?? ''); ?>">
    <p class="growfund-expandable-text__content <?php echo esc_attr($text_class ?? ''); ?>"
        data-max-lines="<?php echo esc_attr($max_lines ?? 2); ?>"
        data-full-text="<?php echo esc_attr($text); ?>">
        <?php echo esc_html($text); ?>
    </p>
    <a href="#" class="growfund-expandable-text__read-more" style="display: none;">
        <?php echo esc_html($read_more_text ?? __('Read more', 'growfund')); ?>
    </a>
    <a href="#" class="growfund-expandable-text__read-less" style="display: none;">
        <?php echo esc_html($read_less_text ?? __('Read less', 'growfund')); ?>
    </a>
</div>