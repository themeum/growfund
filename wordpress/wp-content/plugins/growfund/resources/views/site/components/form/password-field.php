<?php
/**
 * @var Growfund\Views\Components\Form\PasswordField $password_field
 */


defined( 'ABSPATH' ) || exit;

?>

<div class="growfund-password-field-main-wrapper">

    <?php if (!empty($password_field->label)) : ?>
        <label
            class="growfund-password-field-label"
            <?php echo $password_field->id ? 'for="' . esc_attr($password_field->id) . '"' : ''; ?>
        >
            <?php echo esc_html($password_field->label); ?>
        </label>
    <?php endif; ?>

    <div class="growfund-password-field-wrapper">

        <input
            type="password"
            <?php echo $password_field->id ? 'id="' . esc_attr($password_field->id) . '"' : ''; ?>
            <?php echo $password_field->placeholder ? 'placeholder="' . esc_attr($password_field->placeholder) . '"' : ''; ?>
            class="growfund-password-field <?php echo $password_field->classname ? esc_attr(' ' . $password_field->classname) : ''; ?>"
            name="<?php echo esc_attr($password_field->name); ?>"
        />

        <span class="growfund-password-toggle-icon" data-toggle-password>
            <?php growfund_echo_safe_html($password_field->get_eye_icon()); ?>
        </span>

    </div>
    <?php if (!empty($password_field->error_msg)) : ?>
        <span class="growfund-form-error"><?php echo esc_html($password_field->error_msg); ?></spans>
    <?php endif; ?>

</div>