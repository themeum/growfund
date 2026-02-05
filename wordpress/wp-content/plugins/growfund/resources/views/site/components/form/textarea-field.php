<?php
/**
 * @var Growfund\Views\Components\Form\TextareaField $textarea_field
 */

defined( 'ABSPATH' ) || exit;

?>

<div 
    class="growfund-textarea-field <?php echo esc_attr($textarea_field->wrapper_classname); ?>"
    <?php echo $textarea_field->id ? 'id="' . esc_attr($textarea_field->id) . '"' : ''; ?>
    <?php echo $textarea_field->style ? 'style="' . esc_attr($textarea_field->style) . '"' : ''; ?>
>
    <span class="growfund-textarea-label"><?php echo esc_html($textarea_field->label); ?></span>

    <textarea 
        class="growfund-textarea-input <?php echo esc_attr($textarea_field->classname); ?>" 
        name="<?php echo esc_attr($textarea_field->name); ?>" 
        placeholder="<?php echo esc_attr($textarea_field->placeholder); ?>"
    ><?php echo esc_html($textarea_field->value); ?></textarea>
    <?php if (!empty($textarea_field->error_msg)) : ?>
        <span class="growfund-form-error"><?php echo esc_html($textarea_field->error_msg); ?></spans>
    <?php endif; ?>
</div>