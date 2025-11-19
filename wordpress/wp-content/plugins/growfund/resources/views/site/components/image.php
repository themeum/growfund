<?php

defined( 'ABSPATH' ) || exit;

/**
 * Image Component
 * Flexible image component that accepts custom classes and attributes
 * 
 * @param string $src - Image source URL
 * @param string $alt - Alt text for accessibility
 * @param array $attributes - Additional HTML attributes (class, id, data-*, etc.)
 * @param string $class - Custom CSS classes for styling (deprecated, use attributes['class'])
 */

use Growfund\Supports\ClassHelper;

// The renderer uses extract() so these variables are already available
$src = $src ?? '';
$alt = $alt ?? '';
$attributes = $attributes ?? [];
$class = $class ?? '';

// Handle legacy class parameter by merging with attributes
if (!empty($class) && !isset($attributes['class'])) {
    $attributes['class'] = $class;
}

// Build attributes string using helper
$attributeString = ClassHelper::buildAttributesString($attributes, ClassHelper::getDefaultDangerousAttributes());
?>

<img
    src="<?php echo esc_url($src); ?>"
    alt="<?php echo esc_attr($alt); ?>"
    loading="lazy"
    <?php echo $attributeString; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Already escaped during construction ?>>