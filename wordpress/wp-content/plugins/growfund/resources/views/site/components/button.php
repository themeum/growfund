<?php

defined( 'ABSPATH' ) || exit;

/**
 * Button Component
 * Flexible button component that supports variants and custom styling
 * 
 * @param string $text - Button text content
 * @param string $type - Button type (button, submit, reset)
 * @param array $attributes - Additional HTML attributes
 * @param bool $disabled - Whether the button is disabled
 * @param string $icon - Icon name to include (optional)
 * @param string $iconPosition - Icon position (left, right)
 * @param string $variant - Button variant (default, bookmark, search, icon-only, amount-selector)
 * @param string $class - Custom CSS classes for styling
 * @param string $ariaLabel - Aria label for icon-only buttons
 * @param string $onclick - Click event handler
 * @param string $id - Button ID
 * @param string $href - URL for link functionality (optional)
 * @param bool $selected - Whether the button is selected (adds growfund-btn--primary class)
 */

use Growfund\Supports\ClassHelper;

// Set default values for variables (growfund_renderer uses extract() to create individual variables)
$text = $text ?? 'Button';
$type = $type ?? 'button'; // phpcs:ignore -- intentionally used
$attributes = $attributes ?? [];
$disabled = $disabled ?? false;
$icon = $icon ?? null;
$iconPosition = $iconPosition ?? 'left';
$variant = $variant ?? 'default';
$class = $class ?? '';
$ariaLabel = $ariaLabel ?? '';
$onclick = $onclick ?? '';
$id = $id ?? ''; // phpcs:ignore --  intentionally used
$href = $href ?? '';
$selected = $selected ?? false;

// Variant-specific configurations
$variantConfigs = [
    'default' => [
        'classes' => ['growfund-btn'],
        'showText' => true
    ],
    'bookmark' => [
        'classes' => ['growfund-bookmark-btn'],
        'icon' => 'bookmark',
        'iconSize' => 'md',
        'showText' => false,
        'defaultAriaLabel' => 'Bookmark'
    ],
    'bookmark-variant' => [
        'classes' => ['growfund-btn', 'growfund-btn--bookmark'],
        'icon' => 'bookmark',
        'iconSize' => 'sm',
        'showText' => true,
        'defaultAriaLabel' => 'Save for later'
    ],
    'search' => [
        'classes' => ['growfund-search-btn'],
        'icon' => 'search',
        'showText' => false,
        'defaultAriaLabel' => 'Search'
    ],
    'icon-only' => [
        'classes' => ['growfund-icon-btn'],
        'showText' => false,
        'defaultAriaLabel' => 'Button'
    ],
    'back' => [
        'classes' => ['growfund-back-btn'],
        'showText' => false,
        'defaultAriaLabel' => 'Go back'
    ],
    'amount-selector' => [
        'classes' => ['growfund-btn', 'growfund-btn--gray', 'growfund-amount-selector-btn'],
        'showText' => true,
        'defaultAriaLabel' => 'Select amount'
    ]
];

// Get variant configuration
$variantConfig = $variantConfigs[$variant] ?? $variantConfigs['default'];

// Set icon from variant if not explicitly provided
if (!$icon && isset($variantConfig['icon'])) {
    $icon = $variantConfig['icon'];
}

// Set icon size from variant if specified
$iconSize = $variantConfig['iconSize'] ?? 'sm';

// Set aria-label from variant if not explicitly provided
if (!$ariaLabel && isset($variantConfig['defaultAriaLabel'])) {
    $ariaLabel = $variantConfig['defaultAriaLabel'];
}

// Determine if we should show text
$showText = $variantConfig['showText'] ?? true;

// Build class string using helper
$classString = ClassHelper::buildClassStringFromVariant($variantConfigs, $variant, $class);

// Add selected class if needed
if ($selected) {
    $classString .= ' growfund-btn--primary';
}

// Build attributes string using helper
$attributeString = ClassHelper::buildAttributesString($attributes, ClassHelper::getDefaultDangerousAttributes());

// Add aria-label for icon-only buttons
if ($ariaLabel && !$showText) {
    $attributeString .= ' aria-label="' . esc_attr($ariaLabel) . '"';
}

// Add disabled attribute and class if needed
if ($disabled) {
    $attributeString .= ' disabled';
    $classString .= ' growfund-btn--disabled';
}

// Add onclick if provided
if ($onclick) {
    $attributeString .= ' onclick="' . esc_attr($onclick) . '"';
}

// Add id if provided
if ($id) {
    $attributeString .= ' id="' . esc_attr($id) . '"';
}

// Determine if this should be a link or button
$isLink = !empty($href);
?>

<?php if ($isLink) : ?>
    <a href="<?php echo esc_url($href); ?>" class="<?php echo esc_attr($classString); ?>" <?php echo $attributeString; // phpcs:ignore --  already escaped ?>>
    <?php else : ?>
        <button
            type="<?php echo esc_attr($type); ?>"
            class="<?php echo esc_attr($classString); ?>"
            <?php echo $attributeString; // phpcs:ignore --  already escaped ?>>
        <?php endif; ?>

        <?php if ($icon && ($iconPosition === 'left' || !$showText)) : ?>
            <?php
            growfund_renderer()
                ->render('site.components.icon', [
                    'name' => $icon,
                    'size' => $iconSize
                ]);
            ?>
        <?php endif; ?>

        <?php if ($showText) : ?>
            <?php echo esc_html($text); ?>
        <?php endif; ?>

        <?php if ($icon && $iconPosition === 'right' && $showText) : ?>
            <?php

            growfund_renderer()
                ->render('site.components.icon', [
                    'name' => $icon,
                    'size' => $iconSize
                ]);

            ?>
        <?php endif; ?>

        <?php if ($isLink) : ?>
    </a>
<?php else : ?>
    </button>
<?php endif; ?>