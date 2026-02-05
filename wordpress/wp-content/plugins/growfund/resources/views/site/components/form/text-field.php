<?php
/**
 * @var Growfund\Views\Components\Form\TextField $text_field
 */

defined( 'ABSPATH' ) || exit;

$growfund_text_field_has_value = !is_null($text_field->value) && $text_field->value !== '';

?>
<div class="growfund-text-field-main-wrapper">

    <?php if ( ! empty( $text_field->label ) ) : ?>
        <label 
            class="growfund-text-field-label"
            <?php echo $text_field->id ? 'for="' . esc_attr( $text_field->id ) . '"' : ''; ?>
        >
            <?php echo esc_html( $text_field->label ); ?>
        </label>
    <?php endif; ?>
    <div 
    class="growfund-text-field-wrapper"
    <?php echo $text_field->wrapper_style ? ' style="' . esc_attr($text_field->wrapper_style) . '"' : ''; ?>
    >
        <?php if ($text_field->svg_icon) : ?>
            <span class="growfund-text-field-icon">
                <?php growfund_echo_safe_html($text_field->get_text_field_icon()); ?>
            </span>
        <?php endif; ?>

        <input 
            type="text" 
            <?php echo $text_field->id ? 'id="' . esc_attr($text_field->id) . '"' : ''; ?>
            <?php echo $text_field->style ? ' style="' . esc_attr($text_field->style) . '"' : ''; ?>
            <?php echo $text_field->placeholder ? 'placeholder="' . esc_attr($text_field->placeholder) . '"' : ''; ?>
            class="growfund-text-field <?php echo $text_field->classname ? esc_attr(' ' . $text_field->classname) : ''; ?>" 
            name="<?php echo esc_attr($text_field->name); ?>"
            value="<?php echo esc_attr($text_field->value); ?>"
        />

        <?php if ($text_field->allow_clear) : ?>
            <span class="growfund-text-field-clear-icon <?php echo $growfund_text_field_has_value ? esc_attr(' active ') : ''; ?>">
                <?php growfund_echo_safe_html($text_field->get_svg_icon('assets/site/icon/cross.svg')); ?>
            </span>
        <?php endif; ?>
    </div>
    <?php if (!empty($text_field->error_msg)) : ?>
        <span class="growfund-form-error"><?php echo esc_html($text_field->error_msg); ?></spans>
    <?php endif; ?>
</div>

    