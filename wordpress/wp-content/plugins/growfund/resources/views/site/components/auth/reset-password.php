<?php

/** @var Growfund\Views\Components\Auth\ResetPassword $reset_password */

use Growfund\Core\AppSettings;
use Growfund\Supports\Utils;
use Growfund\Views\Components\Form\Button;
use Growfund\Views\Components\Form\PasswordField;
use Growfund\Views\Components\UI\Badge;
use Growfund\Views\Components\UI\Image;

defined( 'ABSPATH' ) || exit;

?>


<div class="growfund-reset-password-main-wrapper">
    <div class="growfund-reset-password-main-wrapper-header">
        <?php
            $growfund_image = new Image();
            $growfund_image->src = growfund_settings(AppSettings::BRANDING)->get_logo('url') ?? growfund_site_image_url('logo.svg');
            $growfund_image->alt = !empty($branding_logo_url) ? growfund_site_name() : __('Growfund', 'growfund');
            $growfund_image->style = growfund_settings(AppSettings::BRANDING)->get_logo_height() 
                ? 'height: ' . growfund_settings(AppSettings::BRANDING)->get_logo_height() . 'px' 
                : '';
            
            growfund_render($growfund_image);
		?>
    </div>
    
    <div class="growfund-reset-password-content">
        <h2 class="growfund-reset-password-title">
            <?php echo esc_html__( 'Reset Password', 'growfund' ); ?>
        </h2>
        <p class="growfund-reset-password-description">
            <?php echo esc_html__( 'Enter your new password below.', 'growfund' ); ?>
        </p>
    </div>

    <?php 
    $growfund_badge = new Badge();
    $growfund_badge->variant = 'error';
    $growfund_badge->message = growfund_flash_get_message('reset_password_error');

    growfund_render($growfund_badge);
    ?>

    <form class="growfund-reset-password-form" method="POST" action="<?php echo esc_url(Utils::get_site_url('growfund/auth/reset-password')); ?>">
        <?php growfund_nonce_field(); ?>

        <input type="hidden" name="key" value="<?php echo esc_attr($reset_password->token); ?>">
        <input type="hidden" name="login" value="<?php echo esc_attr($reset_password->username); ?>">
        
        <div class="growfund-reset-password-field-wrapper">
            <?php
            $growfund_new_password_field = new PasswordField();
            $growfund_new_password_field->label       = __( 'New Password', 'growfund' );
            $growfund_new_password_field->placeholder = __( 'Enter new password', 'growfund' );
            $growfund_new_password_field->name        = 'password';
            $growfund_new_password_field->id          = 'growfund_reset_password_new';
            $growfund_new_password_field->classname   = 'growfund-reset-password-field';
            $growfund_new_password_field->eye_icon    = 'assets/site/icon/eye.svg';
            $growfund_new_password_field->error_msg   = growfund_flash_get_message('reset_password_form_errors')['email'][0] ?? '';
            growfund_render($growfund_new_password_field);
            ?>
        </div>

        <div class="growfund-reset-password-field-wrapper">
            <?php
            $growfund_confirm_password_field = new PasswordField();
            $growfund_confirm_password_field->label       = __( 'Confirm New Password', 'growfund' );
            $growfund_confirm_password_field->placeholder = __( 'Confirm new password', 'growfund' );
            $growfund_confirm_password_field->name        = 'password_confirmation';
            $growfund_confirm_password_field->id          = 'growfund_reset_password_confirm';
            $growfund_confirm_password_field->classname   = 'growfund-reset-password-field';
            $growfund_confirm_password_field->error_msg   = growfund_flash_get_message('reset_password_form_errors')['email'][0] ?? '';
            growfund_render($growfund_confirm_password_field);
            ?>
        </div>

        <div class="growfund-reset-password-button-wrapper">
            <?php
            $growfund_reset_submit_button = new Button();
            $growfund_reset_submit_button->id        = "growfund_reset_password_final_submit_button";
            $growfund_reset_submit_button->classname = 'growfund-reset-password-submit-button growfund-branding-btn';
            $growfund_reset_submit_button->label     = __( 'Reset Password', 'growfund' );
            $growfund_reset_submit_button->type      = 'submit';
            growfund_render($growfund_reset_submit_button);
            ?>
        </div>

        <div class="growfund-reset-password-footer">
            <div class="growfund-reset-password-login-prompt">
                <span class="growfund-reset-password-login-text">
                    <?php echo esc_html__( 'Remembered your password?', 'growfund' ); ?>
                </span>
                <a href="<?php echo esc_url(growfund_login_url()); ?>" class="growfund-reset-password-login-link">
                    <?php echo esc_html__( 'Log in', 'growfund' ); ?>
                </a>
            </div>
        </div>

    </form>
</div>
