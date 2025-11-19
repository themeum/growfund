<?php

defined( 'ABSPATH' ) || exit;

/**
 * Textarea Component
 * Renders a textarea input field with consistent styling
 */

$id = $id ?? ''; // phpcs:ignore -- intentionally used
$name = $name ?? '';
$placeholder = $placeholder ?? '';
$value = $value ?? '';
$required = $required ?? false;
$disabled = $disabled ?? false;
$rows = $rows ?? 4;
$cols = $cols ?? 50;
$maxlength = $maxlength ?? null;
$minlength = $minlength ?? null;
$readonly = $readonly ?? false;
$class = $class ?? 'growfund-textarea';
$attributes = $attributes ?? [];

// Build attributes string
$attributes_string = '';
foreach ($attributes as $key => $value) {
    $attributes_string .= ' ' . esc_attr($key) . '="' . esc_attr($value) . '"';
}

// Build class string
$class_string = $class;
if ($required) {
    $class_string .= ' growfund-required';
}
if ($disabled) {
    $class_string .= ' growfund-disabled';
}

?>

<textarea
    id="<?php echo esc_attr($id); ?>"
    name="<?php echo esc_attr($name); ?>"
    placeholder="<?php echo esc_attr($placeholder); ?>"
    rows="<?php echo esc_attr($rows); ?>"
    cols="<?php echo esc_attr($cols); ?>"
    <?php
    if ($maxlength) :
		?>
        maxlength="<?php echo esc_attr($maxlength); ?>" <?php endif; ?>
    <?php
    if ($minlength) :
		?>
        minlength="<?php echo esc_attr($minlength); ?>" <?php endif; ?>
    <?php
    if ($required) :
		?>
        required<?php endif; ?>
    <?php
    if ($disabled) :
		?>
        disabled<?php endif; ?>
    <?php
    if ($readonly) :
		?>
        readonly<?php endif; ?>
    class="<?php echo esc_attr($class_string); ?>"
    <?php echo $attributes_string; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- already escaped ?>><?php echo esc_textarea($value); ?></textarea>