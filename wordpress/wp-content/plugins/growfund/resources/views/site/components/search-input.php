<?php

defined( 'ABSPATH' ) || exit;

/**
 * Search Input Component
 *
 * @param string $placeholder - Placeholder text
 * @param string $value - Input value
 * @param string $name - Input name attribute
 * @param string $id - Input id attribute
 * @param array $attributes - Additional HTML attributes
 * @param bool $disabled - Whether the input is disabled
 * @param string $class - Custom CSS classes for styling
 * @param string $searchButtonText - Text for search button
 */

// Set default values
$type = 'search'; // phpcs:ignore -- intentionally used
$placeholder = $placeholder ?? '';
$value = $value ?? '';
$name = $name ?? '';
$id = $id ?? '';  // phpcs:ignore -- intentionally used
$attributes = $attributes ?? [];
$disabled = $disabled ?? false;
$class = $class ?? '';
$searchButtonText = $searchButtonText ?? esc_html__('Search', 'growfund');

// Base classes
$baseClasses = ['growfund-search-input'];

// Combine base classes with custom classes
$allClasses = array_merge($baseClasses, explode(' ', trim($class)));
$classString = implode(' ', array_filter($allClasses));

// Build attributes string
$attributeString = '';
foreach ($attributes as $attr_key => $attr_value) {
    $attr_key = trim($attr_key);
    if (empty($attr_key) || !preg_match('/^[a-zA-Z][a-zA-Z0-9\-_]*$/', $attr_key)) {
        continue;
    }
    $dangerousAttributes = ['onload', 'onerror', 'onclick', 'onmouseover', 'onfocus', 'onblur'];
    if (in_array(strtolower($attr_key), $dangerousAttributes, true)) {
        continue;
    }
    $attributeString .= ' ' . esc_attr($attr_key) . '="' . esc_attr($attr_value) . '"';
}

if ($disabled) {
    $attributeString .= ' disabled';
}

if (!$id && !$name) {
    $id = 'search-input-' . wp_generate_password(8, false);  // phpcs:ignore -- intentionally used
}

if ($name) {
    $attributeString .= ' name="' . esc_attr($name) . '"';
}

if ($id) {
    $attributeString .= ' id="' . esc_attr($id) . '"';
}
?>

<div class="growfund-search-box">
    <div class="growfund-search-btn" aria-label="<?php echo esc_attr($searchButtonText); ?>" style="line-height: 0;">
        <?php
        growfund_renderer()
            ->render('site.components.icon', [
                'name' => 'search',
                'size' => 'sm'
            ]);
		?>
    </div>
    <input
        type="<?php echo esc_attr($type); ?>"
        class="<?php echo esc_attr($classString); ?>"
        placeholder="<?php echo esc_attr($placeholder); ?>"
        value="<?php echo esc_attr($value); ?>"
        <?php
        echo $attributeString; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Already escaped during construction 
        ?>
        />
</div>