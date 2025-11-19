<?php

defined( 'ABSPATH' ) || exit;

/**
 * Badge Component
 * Flexible badge component for displaying status, categories, or labels
 * 
 * @param string $text - Badge text content
 * @param string $variant - Badge variant (default, success, warning, danger, info)
 * @param string $class - Custom CSS classes for styling
 * @param array $attributes - Additional HTML attributes
 */

use Growfund\Supports\ClassHelper;

$text = $text ?? '';
$variant = $variant ?? 'default';
$class = $class ?? '';
$attributes = $attributes ?? [];

// Variant-specific configurations
$variantConfigs = [
    'default' => [
        'classes' => ['growfund-badge']
    ],
    'success' => [
        'classes' => ['growfund-badge', 'growfund-badge-success']
    ],
    'warning' => [
        'classes' => ['growfund-badge', 'growfund-badge-warning']
    ],
    'danger' => [
        'classes' => ['growfund-badge', 'growfund-badge-danger']
    ],
    'info' => [
        'classes' => ['growfund-badge', 'growfund-badge-info']
    ]
];

// Build class string using helper
$classString = ClassHelper::buildClassStringFromVariant($variantConfigs, $variant, $class);

// Build attributes string using helper
$attributeString = ClassHelper::buildAttributesString($attributes, ClassHelper::getDefaultDangerousAttributes());
?>

<span
    class="<?php echo esc_attr($classString); ?>"
    <?php echo $attributeString; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Already escaped ?>>
    <?php echo esc_html($text); ?>
</span>