<?php
/**
 * @var Growfund\Views\Components\Form\NumberField $number_field
 */

use Growfund\Supports\Currency;

defined( 'ABSPATH' ) || exit;

// Updated to check number_field value
$growfund_number_field_has_value = !is_null($number_field->value) && $number_field->value !== '';

?>
<div class="growfund-number-field-main-wrapper">
    <?php if (!empty($number_field->label)) : ?>
        <label
            class="growfund-number-field-label"
            <?php echo $number_field->id ? 'for="' . esc_attr($number_field->id) . '"' : ''; ?>
        >
            <?php echo esc_html( $number_field->label ); ?>
        </label>
    <?php endif; ?>
    <div class="growfund-number-field-wrapper">

        <?php if ($number_field->show_currency && Currency::get_currency_position() === 'before') : ?>
            <span class="growfund-number-field-currency">
                <?php echo esc_html(Currency::get_symbol()); ?>
            </span>
        <?php endif; ?>

        <input 
            type="number" 
            <?php echo $number_field->id ? 'id="' . esc_attr($number_field->id) . '"' : ''; ?>
            <?php echo $number_field->style ? ' style="' . esc_attr($number_field->style) . '"' : ''; ?>
            <?php echo $number_field->placeholder ? 'placeholder="' . esc_attr($number_field->placeholder) . '"' : ''; ?>
            class="growfund-number-field hide-arrows" 
            name="<?php echo esc_attr($number_field->name); ?>"
            value="<?php echo esc_attr($number_field->value); ?>"
            <?php echo isset($number_field->min) ? 'min="' . esc_attr($number_field->min) . '"' : ''; ?>
            <?php echo isset($number_field->max) ? 'max="' . esc_attr($number_field->max) . '"' : ''; ?>
        />

        <?php if ($number_field->show_currency && Currency::get_currency_position() === 'after') : ?>
            <span class="growfund-number-field-currency">
                <?php echo esc_html(Currency::get_symbol()); ?>
            </span>
        <?php endif; ?>

        
    </div>
    <?php if (!empty($number_field->error_msg)) : ?>
        <span class="growfund-form-error"><?php echo esc_html($number_field->error_msg); ?></span>
    <?php endif; ?>
</div>
