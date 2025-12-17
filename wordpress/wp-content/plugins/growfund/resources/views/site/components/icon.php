<?php

defined( 'ABSPATH' ) || exit;

/**
 * Icon Component
 * Dynamically loads SVG icons from assets/icon folder
 * 
 * @var string $name Icon name (heart, comment, bell, play, etc.) - must match filename in assets/icon/
 * @var string $size Icon size (xs, sm, md, lg, xl) or custom pixel size
 * @var string $color Icon color (defaults to currentColor)
 * @var array $attributes Additional HTML attributes for the wrapper div
 */

use Growfund\Supports\ClassHelper;

$name = $name ?? 'placeholder';
$size = $size ?? 'md';
$color = $color ?? 'currentColor';
$attributes = $attributes ?? [];

// Size mapping
$sizeMap = [
    'xs' => '12px',
    'sm' => '16px',
    'md' => '20px',
    'lg' => '24px',
    'xl' => '32px'
];

// Get icon dimensions
$iconSize = is_numeric($size) ? $size . 'px' : ($sizeMap[$size] ?? '20px');

// Build attributes string using helper
$attributesStr = ClassHelper::buildAttributesString($attributes, ClassHelper::getDefaultDangerousAttributes());

// Construct icon file path - using WordPress plugin directory
$iconBasePath = GROWFUND_DIR_PATH . '/resources/assets/site/icon/';
$iconFile = $iconBasePath . sanitize_file_name($name) . '.svg';
$fallbackIconFile = $iconBasePath . 'placeholder.svg';

// Load icon SVG content with WordPress security practices
$iconContent = '';
if (file_exists($iconFile) && is_readable($iconFile)) {
    $iconContent = file_get_contents($iconFile);
} elseif (file_exists($fallbackIconFile) && is_readable($fallbackIconFile)) {
    $iconContent = file_get_contents($fallbackIconFile);
}

// Sanitize SVG content - remove any potential script tags for security
$iconContent = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi', '', $iconContent);
$iconContent = str_replace(['<script', '</script>'], ['&lt;script', '&lt;/script&gt;'], $iconContent);

// Preload common icons for easy JavaScript access
$commonIcons = ['bookmark', 'check-circled', 'heart', 'comment', 'search', 'play', 'pause'];
$preloadedIcons = [];

foreach ($commonIcons as $iconName) {
    $icon_path = $iconBasePath . $iconName . '.svg';
    if (file_exists($icon_path) && is_readable($icon_path)) {
        $icon_data = file_get_contents($icon_path);
        $preloadedIcons[$iconName] = $icon_data;
    }
}

?>

<div
    class="growfund-icon growfund-icon--<?php echo esc_attr($name); ?> growfund-icon--<?php echo esc_attr($size); ?>"
    style="color: <?php echo esc_attr($color); ?>; width: <?php echo esc_attr($iconSize); ?>; height: <?php echo esc_attr($iconSize); ?>;"
    data-icon-name="<?php echo esc_attr($name); ?>"
    data-preloaded-icons="<?php echo esc_attr(wp_json_encode($preloadedIcons)); ?>"
    <?php echo $attributesStr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- already escaped ?>>
    <?php
    // Output the SVG content
    echo wp_kses_post($iconContent);
    ?>
</div>