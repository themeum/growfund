<?php

defined( 'ABSPATH' ) || exit;

/**
 * Comments Fallback Component
 * 
 * Displays a fallback state when comments are temporarily unavailable or loading.
 * 
 * @param array $args {
 *     @type string $state    The fallback state: 'unavailable' or 'loading'
 *     @type string $title    Custom title for the fallback state
 *     @type string $message  Custom message for the fallback state
 * }
 */

$state = $state ?? 'unavailable';
$title = $title ?? ''; // phpcs:ignore -- intentionally used
$message = $message ?? '';

if (empty($title)) {
    $title = $state === 'loading' // phpcs:ignore -- intentionally used
        ? __('Comments', 'growfund')
        : __('Comments', 'growfund');
}

if (empty($message)) {
    $message = $state === 'loading'
        ? __('Comments are currently being loaded...', 'growfund')
        : __('Comments temporarily unavailable.', 'growfund');
}
?>

<div class="growfund-comments__main">
    <div class="growfund-card growfund-comments__form-wrapper">
        <p><?php echo esc_html($message); ?></p>
    </div>
    <div class="growfund-card growfund-comments__list">
        <div class="growfund-no-comments">
            <h3><?php echo esc_html($title); ?></h3>
            <p><?php echo esc_html($message); ?></p>
        </div>
    </div>
</div>