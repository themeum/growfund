<?php
/**
 * @var Growfund\Views\Components\Form\CheckboxField $checkbox_field
 */

defined( 'ABSPATH' ) || exit;
?>

<label class="growfund-checkbox-field">
    <input 
        type="checkbox" 
        name="<?php echo esc_attr($checkbox_field->name); ?>" 
        value="<?php echo esc_attr($checkbox_field->checked ? 'true' : 'false'); ?>" 
        <?php echo esc_attr(checked($checkbox_field->checked)); ?>
        class="growfund-checkbox-input<?php echo esc_attr($checkbox_field->classname ? ' ' . $checkbox_field->classname : ''); ?>"
        <?php echo $checkbox_field->id ? 'id="' . esc_attr($checkbox_field->id) . '"' : ''; ?>
        <?php echo $checkbox_field->style ? ' style="' . esc_attr($checkbox_field->style) . '"' : ''; ?>
    />

    <?php if ( ! empty( $checkbox_field->label ) ) : ?>
        <span
        class="growfund-checkbox-field-label
        <?php echo esc_attr(!empty($checkbox_field->label_class) ? ' ' . $checkbox_field->label_class : ''); ?>
        "
    >
            <?php growfund_echo_safe_html($checkbox_field->label); ?>
        </span>
    <?php endif; ?>
</label>
<?php if (!empty($checkbox_field->error_msg)) : ?>
    <p class="growfund-form-error"><?php echo esc_html($checkbox_field->error_msg); ?></p>
<?php endif; ?>
