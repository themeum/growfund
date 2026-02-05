<?php
/**
 * @var Growfund\Views\Components\Form\RadioField $radio_field
 */

defined( 'ABSPATH' ) || exit;
?>

<label 
class="growfund-radio-field <?php echo $radio_field->wrapper_class ? esc_attr($radio_field->wrapper_class) : ''; ?>"
<?php echo $radio_field->title ? 'title="' . esc_attr($radio_field->title) . '"' : ''; ?>
>
    <?php if ($radio_field->icon) : ?>
        <span><?php growfund_echo_safe_html($radio_field->icon); ?></span>
    <?php endif; ?>  
    <input 
        type="radio" 
        class="growfund-radio-input <?php echo $radio_field->classname ? ' ' . esc_attr($radio_field->classname) : ''; ?>"
        <?php echo $radio_field->id ? 'id="' . esc_attr($radio_field->id) . '"' : ''; ?>
        <?php echo $radio_field->style ? ' style="' . esc_attr($radio_field->style) . '"' : ''; ?>
        name="<?php echo esc_attr($radio_field->name); ?>" 
        value="<?php echo esc_attr($radio_field->value); ?>" 
        <?php checked($radio_field->checked, true); ?>
    />
    <span class="growfund-radio-field-label">
        <?php echo esc_html($radio_field->label); ?>
    </span>
</label>
    
