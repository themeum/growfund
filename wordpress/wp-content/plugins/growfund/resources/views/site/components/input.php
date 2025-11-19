<?php

defined( 'ABSPATH' ) || exit;

/**
 * Input Component
 * Flexible input component that supports different variants and custom styling
 * 
 * @param string $type - Input type (text, email, password, etc.)
 * @param string $placeholder - Placeholder text
 * @param string $value - Input value
 * @param string $name - Input name attribute
 * @param string $id - Input id attribute
 * @param array $attributes - Additional HTML attributes
 * @param bool $disabled - Whether the input is disabled
 * @param bool $required - Whether the input is required
 * @param string $variant - Input variant (default, search, currency, simple, etc.)
 * @param string $class - Custom CSS classes for styling
 * @param array $baseClasses - Base classes that are always applied (optional override)
 * @param string $searchButtonText - Text for search button (only for search variant)
 * @param string $searchIcon - Icon for search button (only for search variant)
 * @param string $min - Minimum value for number inputs
 */

use Growfund\Core\CurrencyConfig;
use Growfund\Supports\ClassHelper;

// Set default values for variables (growfund_renderer uses extract() to create individual variables)
$type = $type ?? 'text'; // phpcs:ignore -- intentionally used
$placeholder = $placeholder ?? '';
$value = $value ?? '';
$name = $name ?? '';
$id = $id ?? ''; // phpcs:ignore -- intentionally used
$attributes = $attributes ?? [];
$disabled = $disabled ?? false;
$required = $required ?? false;
$variant = $variant ?? 'default';
$class = $class ?? '';
$min = $min ?? '';
$checked = $checked ?? false;

if ($variant === 'currency' || $variant === 'simple') {
    try {
        $currency_config = growfund_app()->make(CurrencyConfig::class)->get();
        $currency_parts = explode(':', $currency_config['currency']);
        $currencySymbol = $currency_parts[0] ?? '$';
    } catch (Exception $error) {
        $currencySymbol = '$';
    }
}

// Validate input type against allowed HTML5 input types for security
$allowedTypes = [
    'text',
    'email',
    'password',
    'tel',
    'url',
    'search',
    'number',
    'range',
    'date',
    'datetime-local',
    'month',
    'time',
    'week',
    'color',
    'file',
    'hidden',
    'checkbox',
    'radio'
];
if (!in_array($type, $allowedTypes, true)) {
    $type = 'text'; // phpcs:ignore -- Fallback to safe default
}

// Variant-specific classes
$variantConfigs = [
    'default' => [
        'classes' => ['growfund-input']
    ],
    'simple' => [
        'classes' => ['growfund-input-simple']
    ]
];

// Build class string using helper
$classString = ClassHelper::buildClassStringFromVariant($variantConfigs, $variant, $class);

// Build attributes string using helper
$attributeString = ClassHelper::buildAttributesString($attributes, ClassHelper::getDefaultDangerousAttributes());

// Add disabled attribute if needed
if ($disabled) {
    $attributeString .= ' disabled';
}

// Add required attribute if needed
if ($required) {
    $attributeString .= ' required';
}

// Add min attribute for number inputs
if ($min !== '' && $type === 'number') {
    $attributeString .= ' min="' . esc_attr($min) . '"';
}

// Add checked attribute for radio and checkbox inputs
if ($checked && ($type === 'radio' || $type === 'checkbox')) {
    $attributeString .= ' checked';
}

// For number type, step by 1 but display 2 decimals
if ($type === 'number') {
    $attributeString .= ' step="1"';
    // Format value to 2 decimal places for display
    if (is_numeric($value)) {
        $value = number_format((float) $value, 2, '.', '');
    }
}

// Ensure at least id or name is present for accessibility
if (!$id && !$name) {
    // Generate a more secure unique id
    $defaultId = 'input-' . wp_generate_password(8, false);
    $id = $defaultId; // phpcs:ignore -- intentionally used
} elseif (!$id && $name) {
    // Generate ID based on name for proper label association
    $id = 'input-' . sanitize_title($name); // phpcs:ignore -- intentionally used
}

// Add name attribute if provided
if ($name) {
    $attributeString .= ' name="' . esc_attr($name) . '"';
}

// Add id attribute if provided
if ($id) {
    $attributeString .= ' id="' . esc_attr($id) . '"';
}
?>

<?php if ($variant === 'currency') : ?>
    <div class="growfund-amount-input">
        <span class="growfund-currency"><?php echo esc_html($currencySymbol); ?></span>
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
<?php elseif ($variant === 'simple') : ?>
    <div class="growfund-simple-amount-input">
        <span class="growfund-simple-currency"><?php echo esc_html($currencySymbol); ?></span>
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
<?php elseif ($type === 'password') : ?>
    <div class="growfund-password-wrapper">
        <input
            type="<?php echo esc_attr($type); ?>"
            class="<?php echo esc_attr($classString); ?>"
            placeholder="<?php echo esc_attr($placeholder); ?>"
            value="<?php echo esc_attr($value); ?>"
            <?php
            echo $attributeString; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Already escaped during construction 
            ?>
            />
        <a href="#" class="growfund-password-toggle" aria-label="<?php esc_html_e('Toggle password visibility', 'growfund'); ?>" data-password-visible="false">
            <span class="growfund-password-icon growfund-password-icon--hidden">
                <?php
                growfund_renderer()->render('site.components.icon', [
					'name' => 'password-mask',
					'size' => 'sm'
				]);
                ?>
            </span>
            <span class="growfund-password-icon growfund-password-icon--visible">
                <?php
                growfund_renderer()->render('site.components.icon', [
					'name' => 'eye',
					'size' => 'sm'
				]);
                ?>
            </span>
        </a>
    </div>
<?php else : ?>
    <input
        type="<?php echo esc_attr($type); ?>"
        class="<?php echo esc_attr($classString); ?>"
        placeholder="<?php echo esc_attr($placeholder); ?>"
        value="<?php echo esc_attr($value); ?>"
        <?php
        echo $attributeString; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Already escaped during construction 
        ?>
        />
<?php endif; ?>