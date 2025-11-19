<?php

defined( 'ABSPATH' ) || exit;

/**
 * Dropdown Component
 * Flexible dropdown component that supports different variants and custom styling
 * 
 * @param array $options - Array of options with 'value' and 'label' keys
 * @param string $placeholder - Placeholder text when no option is selected (defaults to "Select an option...")
 * @param string $value - Selected value
 * @param string $name - Input name attribute
 * @param string $id - Input id attribute
 * @param array $attributes - Additional HTML attributes
 * @param bool $disabled - Whether the dropdown is disabled
 * @param bool $required - Whether the dropdown is required
 * @param string $variant - Dropdown variant (default, search, nested, location, gray)
 * @param string $class - Custom CSS classes for styling
 * @param bool $searchable - Whether the dropdown is searchable
 * @param string $searchPlaceholder - Placeholder text for search input (only for searchable dropdowns)
 * @param string $emptyMessage - Message to show when no options are found
 * @param bool $multiple - Whether multiple selection is allowed
 * 
 * Example usage with custom placeholder:
 * 
 * 1. Direct component usage:
 * growfund_renderer()->render('site.components.dropdown', [
 *     'options' => [...],
 *     'name' => 'country',
 *     'placeholder' => __('Choose your country', 'growfund')
 * ]);
 * 
 * 2. Via form builder:
 * [
 *     'type' => 'dropdown',
 *     'data' => [
 *         'options' => [...],
 *         'name' => 'payment_method',
 *         'placeholder' => __('Select payment method', 'growfund')
 *     ]
 * ]
 * 
 * 3. With translations:
 * 'placeholder' => __('Select your preferred option', 'growfund')
 * 
 * 4. With gray variant for donation forms:
 * 'variant' => 'gray'
 */

$options = $options ?? [];
$placeholder = $placeholder ?? __('Select an option...', 'growfund');
$value = $value ?? '';
$name = $name ?? '';
$id = $id ?? ''; // phpcs:ignore -- intentionally used
$attributes = $attributes ?? [];
$disabled = $disabled ?? false;
$required = $required ?? false;
$variant = $variant ?? 'default';
$class = $class ?? '';
$searchable = $searchable ?? false;
$searchPlaceholder = $searchPlaceholder ?? __('Search options...', 'growfund');
$emptyMessage = $emptyMessage ?? __('No options found', 'growfund');
$multiple = $multiple ?? false;

if ('location' === $variant) {
    $searchable = true;
    if (empty($options)) {
        $options = [];
    }
}

$is_nested_variant = $variant === 'nested';


$variantClasses = [
    'default' => [
        'growfund-dropdown'
    ],
    'search' => [
        'growfund-dropdown',
        'growfund-dropdown--searchable'
    ],
    'nested' => [
        'growfund-dropdown',
        'growfund-dropdown--nested'
    ],
    'location' => [
        'growfund-dropdown',
        'growfund-dropdown--searchable',
        'growfund-dropdown--location',
    ],
    'gray' => [
        'growfund-dropdown',
        'growfund-dropdown--gray'
    ]
];


$currentVariantClasses = $variantClasses[$variant] ?? $variantClasses['default'];


$allClasses = array_merge($currentVariantClasses, explode(' ', trim($class)));


$hasErrors = isset($attributes['data-has-errors']) && $attributes['data-has-errors'] === 'true';
if ($hasErrors) {
    $allClasses[] = 'growfund-dropdown--error';
}


$classString = implode(' ', array_filter($allClasses));


$attributeString = '';
foreach ($attributes as $key => $attrValue) {
    $attributeString .= ' ' . esc_attr($key) . '="' . esc_attr($attrValue) . '"';
}

if ($disabled) {
    $attributeString .= ' disabled';
}


if ($required) {
    $attributeString .= ' required';
}


if (!$id && !$name) {

    $defaultId = 'dropdown-' . uniqid();
    $id = $defaultId; // phpcs:ignore -- intentionally used
}


$dropdownId = $id ? $id : 'dropdown-' . uniqid();
$listboxId = $dropdownId . '-listbox';
$searchId = $dropdownId . '-search';


$selectedOption = null;
if ($value) {
    $findOption = function ($options, $value) use (&$findOption) {
        foreach ($options as $option) {
            if (isset($option['value']) && $option['value'] === $value) {
                return $option;
            }
            if (isset($option['children']) && is_array($option['children'])) {
                $found = $findOption($option['children'], $value);
                if ($found) {
                    return $found;
                }
            }
        }
        return null;
    };
    $selectedOption = $findOption($options, $value);
}


$displayText = $selectedOption ? ($selectedOption['label'] ?? $selectedOption['value']) : $placeholder;
?>

<div class="<?php echo esc_attr($classString); ?>"
    data-dropdown
    data-initial-value="<?php echo esc_attr($value); ?>"
    data-value="<?php echo esc_attr($value); ?>"
    <?php echo $attributeString; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Already escaped ?>
    data-dropdown-key="<?php echo esc_attr($name); ?>"
    data-placeholder="<?php echo esc_attr($placeholder); ?>">


    <select name="<?php echo esc_attr($name); ?>"
        id="<?php echo esc_attr($dropdownId); ?>"
        style="display: none;"
        <?php echo $required ? 'required' : ''; ?>
        <?php echo ($disabled) ? 'disabled' : ''; ?>
        <?php echo $multiple ? 'multiple' : ''; ?>>
        <?php if (!$required && !$multiple) : ?>
            <option value=""><?php echo esc_html($placeholder); ?></option>
        <?php endif; ?>
        <?php foreach ($options as $option) : ?>
            <option value="<?php echo esc_attr($option['value'] ?? ''); ?>"
                <?php echo ($option['value'] ?? '') === $value ? 'selected' : ''; ?>>
                <?php echo esc_html($option['label'] ?? $option['value'] ?? ''); ?>
            </option>
        <?php endforeach; ?>
    </select>


    <button type="button"
        class="growfund-dropdown__trigger"
        aria-haspopup="listbox"
        aria-expanded="false"
        aria-labelledby="<?php echo esc_attr($dropdownId); ?>"
        <?php echo $disabled ? 'disabled' : ''; ?>>
        <span class="growfund-dropdown__value-container">
            <span class="growfund-dropdown__value"><?php echo esc_html($displayText); ?></span>
            <span class="growfund-dropdown__arrow">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="6,9 12,15 18,9"></polyline>
                </svg>
            </span>
        </span>

        <?php if (!$multiple) : ?>
            <span class="growfund-dropdown__clear-action">
                <span class="growfund-dropdown__clear-icon">
                    <?php
                    growfund_renderer()
                        ->render('site.components.icon', [
                            'name' => 'cross',
                            'size' => 'sm'
                        ]);
					?>
                </span>
            </span>
        <?php endif; ?>
    </button>


    <div class="growfund-dropdown__panel"
        role="listbox"
        id="<?php echo esc_attr($listboxId); ?>"
        aria-labelledby="<?php echo esc_attr($dropdownId); ?>">

        <?php if ($searchable) : ?>
            <div class="growfund-dropdown__search">
                <?php
                growfund_renderer()
                    ->render('site.components.search-input', [
                        'id' => $searchId,
                        'class' => 'growfund-dropdown__search-input',
                        'placeholder' => $searchPlaceholder,
                        'attributes' => [
                            'autocomplete' => 'off',
                            'aria-label' => __('Search options', 'growfund'),
                        ],
                    ]);
                ?>
            </div>
        <?php endif; ?>

        <ul class="growfund-dropdown__list" role="none">
            <?php if (empty($options)) : ?>
                <li class="growfund-dropdown__empty" role="option" aria-disabled="true">
                    <?php echo esc_html($emptyMessage); ?>
                </li>
            <?php else : ?>
                <?php foreach ($options as $index => $option) : ?>
                    <?php

                    $isCategory = isset($option['children']) && is_array($option['children']);
                    $optionValue = $option['value'] ?? '';
                    $optionLabel = $option['label'] ?? $option['value'] ?? '';
                    $isSelected = ($optionValue === $value);
                    $categoryExpanded = false;
                    ?>

                    <?php if ($is_nested_variant && $isCategory) : ?>
                        <?php if (!empty($option['children'])) : ?>
                            <li class="growfund-dropdown__category-item"
                                data-value="<?php echo esc_attr($optionValue); ?>"
                                data-label="<?php echo esc_attr($optionLabel); ?>">
                                <button type="button" class="growfund-dropdown__category-trigger" aria-expanded="false">
                                    <span class="growfund-dropdown__category-text"><?php echo esc_html($optionLabel); ?></span>
                                    <span class="growfund-dropdown__category-arrow">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="6,9 12,15 18,9"></polyline>
                                        </svg>
                                    </span>
                                </button>
                                <ul class="growfund-dropdown__sub-list">
                                    <?php foreach ($option['children'] as $childOption) : ?>
                                        <?php
                                        $childValue = $childOption['value'] ?? '';
                                        $childLabel = $childOption['label'] ?? $childOption['value'] ?? '';
                                        $isChildSelected = ($childValue === $value);
                                        ?>
                                        <li class="growfund-dropdown__option growfund-dropdown__option--nested"
                                            role="option"
                                            tabindex="-1"
                                            aria-selected="<?php echo $isChildSelected ? 'true' : 'false'; ?>"
                                            data-value="<?php echo esc_attr($childValue); ?>"
                                            data-label="<?php echo esc_attr($childLabel); ?>">
                                            <label class="growfund-dropdown__option-label">
                                                <input type="radio"
                                                    name="<?php echo esc_attr($name); ?>"
                                                    value="<?php echo esc_attr($childValue); ?>"
                                                    class="growfund-dropdown__option-radio"
                                                    <?php echo $isChildSelected ? 'checked' : ''; ?>
                                                    <?php echo $disabled ? 'disabled' : ''; ?>>
                                                <?php if ('location' === $variant) : ?>
                                                    <span class="growfund-dropdown__option-icon">
                                                        <?php
                                                        growfund_renderer()
                                                            ->render('site.components.icon', [
                                                                'name' => 'location',
                                                                'size' => 'sm'
                                                            ]);
														?>
                                                    </span>
                                                <?php elseif (isset($childOption['icon'])) : ?>
                                                    <span class="growfund-dropdown__option-icon">
                                                        <?php
                                                        growfund_renderer()
                                                            ->render('site.components.icon', [
                                                                'name' => $childOption['icon'],
                                                                'size' => 'sm'
                                                            ]);
														?>
                                                    </span>
                                                <?php endif; ?>
                                                <span class="growfund-dropdown__option-text">
                                                    <?php echo esc_html($childLabel); ?>
                                                </span>
                                            </label>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </li>
                        <?php else : ?>
                            <li class="growfund-dropdown__option growfund-dropdown__option--category"
                                role="option"
                                tabindex="-1"
                                aria-selected="<?php echo $isSelected ? 'true' : 'false'; ?>"
                                data-value="<?php echo esc_attr($optionValue); ?>"
                                data-label="<?php echo esc_attr($optionLabel); ?>">
                                <?php if ('location' === $variant) : ?>
                                    <span class="growfund-dropdown__option-icon">
                                        <?php
                                        growfund_renderer()
                                            ->render('site.components.icon', [
                                                'name' => 'location',
                                                'size' => 'sm'
                                            ]);
										?>
                                    </span>
                                <?php elseif (isset($option['icon'])) : ?>
                                    <span class="growfund-dropdown__option-icon">
                                        <?php
                                        growfund_renderer()
                                            ->render('site.components.icon', [
                                                'name' => $option['icon'],
                                                'size' => 'sm'
                                            ]);
										?>
                                    </span>
                                <?php endif; ?>
                                <span class="growfund-dropdown__option-text">
                                    <?php echo esc_html($optionLabel); ?>
                                </span>
                            </li>
                        <?php endif; ?>
                    <?php else : ?>
                        <li class="growfund-dropdown__option"
                            role="option"
                            tabindex="-1"
                            aria-selected="<?php echo $isSelected ? 'true' : 'false'; ?>"
                            data-value="<?php echo esc_attr($optionValue); ?>"
                            data-label="<?php echo esc_attr($optionLabel); ?>">
                            <?php if ('location' === $variant) : ?>
                                <span class="growfund-dropdown__option-icon">
                                    <?php
                                    growfund_renderer()
                                        ->render('site.components.icon', [
                                            'name' => 'location',
                                            'size' => 'sm'
                                        ]);
									?>
                                </span>
                            <?php elseif (isset($option['icon'])) : ?>
                                <span class="growfund-dropdown__option-icon">
                                    <?php
                                    growfund_renderer()
                                        ->render('site.components.icon', [
                                            'name' => $option['icon'],
                                            'size' => 'sm'
                                        ]);
									?>
                                </span>
                            <?php endif; ?>
                            <span class="growfund-dropdown__option-text">
                                <?php echo esc_html($optionLabel); ?>
                            </span>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>

        <?php if (!$multiple && 'location' !== $variant) : ?>
            <div class="growfund-dropdown__footer" <?php echo empty($value) ? 'style="display:none;"' : ''; ?>>
                <a type="button" class="growfund-dropdown__clear-all">
                    <?php esc_html_e('Clear all', 'growfund'); ?>
                </a>
            </div>
        <?php endif; ?>

    </div>
</div>