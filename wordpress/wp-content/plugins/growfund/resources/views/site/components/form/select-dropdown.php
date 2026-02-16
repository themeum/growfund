<?php
/**
 * Growfund Dropdown Filter
 * @var Growfund\Views\Components\Form\SelectDropdown $select_dropdown
 */

use Growfund\Supports\Arr;

defined( 'ABSPATH' ) || exit;

$growfund_selected_option = Arr::make($select_dropdown->options)->find(function ($growfund_option) use ($select_dropdown) {
    return $growfund_option['value'] === $select_dropdown->default_value;
});

$growfund_selected_label = $growfund_selected_option['label'] ?? '';

$growfund_has_default_dropdown_value = !is_null($select_dropdown->default_value) && $select_dropdown->default_value !== '';


?>

<div
    <?php echo $select_dropdown->id ? 'id="' . esc_attr($select_dropdown->id) . '"' : ''; ?>
    <?php echo $select_dropdown->style ? 'style="' . esc_attr($select_dropdown->style) . '"' : ''; ?>
    class="growfund-select-dropdown <?php echo esc_attr($select_dropdown->classname ?? ''); ?>"
    data-name="<?php echo esc_attr($select_dropdown->name); ?>"
    data-selected-value="<?php echo esc_attr($select_dropdown->default_value ?? ''); ?>"
    data-filterable="<?php echo esc_html($select_dropdown->is_filterable ? 'true' : 'false'); ?>"
>
    <input
        type="hidden"
        name="<?php echo esc_attr($select_dropdown->name); ?>"
        value="<?php echo esc_attr($select_dropdown->default_value ?? ''); ?>"
        class="growfund-select-dropdown-input"
    />

    <div class="growfund-select-dropdown-label-wrapper">
        <div class="growfund-select-dropdown-label-inner">
            <span class="growfund-select-dropdown-placeholder <?php echo esc_attr(!$growfund_has_default_dropdown_value ? 'show' : ''); ?>">
                <?php echo esc_html($select_dropdown->placeholder ?? __('Select', 'growfund')); ?>
            </span>

            <span class="growfund-select-dropdown-label <?php echo esc_attr($growfund_has_default_dropdown_value ? 'show' : ''); ?>">
                <?php echo esc_html($growfund_selected_label); ?>
            </span>

            <span class="growfund-select-dropdown-arrow-icon <?php echo esc_attr($growfund_has_default_dropdown_value ? 'growfund-hidden' : ''); ?>">
                <?php
                growfund_echo_safe_html(
                    $select_dropdown->get_svg_icon('assets/site/icon/arrow-down.svg')
                );
                ?>
            </span>
        </div>

        <?php if (!empty($select_dropdown->allow_clear)) : ?>
            <span class="growfund-select-dropdown-clear-icon <?php echo esc_attr($growfund_has_default_dropdown_value ? 'active' : ''); ?>">
                <?php
                growfund_echo_safe_html(
                    $select_dropdown->get_svg_icon('assets/site/icon/cross.svg')
                );
                ?>
            </span>
        <?php endif; ?>
    </div>

    <div class="growfund-select-dropdown-menu">
        <div class="growfund-select-dropdown-menu-options">
            <?php foreach ($select_dropdown->options as $growfund_option) : ?>
                <div
                    class="growfund-select-dropdown-item <?php echo ((string) $growfund_option['value'] === (string) $select_dropdown->default_value ?? '') ? 'selected' : ''; ?>"
                    data-value="<?php echo esc_attr($growfund_option['value']); ?>"
                >
                    <?php echo esc_html($growfund_option['label']); ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php if (!empty($select_dropdown->error_msg)) : ?>
        <span class="growfund-form-error"><?php echo esc_html($select_dropdown->error_msg); ?></span>
    <?php endif; ?>
</div>
