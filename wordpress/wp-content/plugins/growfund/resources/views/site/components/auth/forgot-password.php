<?php

/** @var Growfund\Views\Components\Auth\ForgotPassword $forgot_password */

use Growfund\Core\AppSettings;
use Growfund\Supports\Utils;
use Growfund\Views\Components\Form\Button;
use Growfund\Views\Components\Form\TextField;
use Growfund\Views\Components\UI\Badge;
use Growfund\Views\Components\UI\Image;

defined( 'ABSPATH' ) || exit;

?>


<div class="growfund-forgot-password-main-wrapper">
    <div class="growfund-forgot-password-main-wrapper-header">
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
    
    <div class="growfund-forgot-password-content">
        <h2 class="growfund-forgot-password-title">
            <?php echo esc_html__( 'Enter your email address', 'growfund' ); ?>
        </h2>
        <p class="growfund-forgot-password-description">
            <?php echo esc_html__( 'No worries! Enter your email address and we\'ll send you a link to reset your password.', 'growfund' ); ?>
        </p>
    </div>

    <?php 
    $growfund_badge = new Badge();
    $growfund_badge->variant = 'error';
    $growfund_badge->message = growfund_flash_get_message('forgot_password_error');

    growfund_render($growfund_badge);
    ?>

    <form class="growfund-forgot-password-form" method="POST" action="<?php echo esc_url(Utils::get_site_url('growfund/auth/forgot-password')); ?>">
        <?php growfund_nonce_field(); ?>
        
        <div class="growfund-forgot-password-field-wrapper">
            <?php
            $growfund_email_field = new TextField();
            $growfund_email_field->label       = __('Email address', 'growfund');
            $growfund_email_field->placeholder = __('e.g. johnsmith@yourmail.com', 'growfund');
            $growfund_email_field->name        = 'email';
            $growfund_email_field->id          = 'growfund_forgot_password_input';
            $growfund_email_field->classname   = 'growfund-forgot-password-text-field';
            $growfund_email_field->value   = growfund_flash_get_message('forgot_password_form_old')['email'] ?? '';
            $growfund_email_field->error_msg   = growfund_flash_get_message('forgot_password_form_errors')['email'][0] ?? '';

            growfund_render( $growfund_email_field );
            ?>
        </div>

      
            <?php
            $growfund_reset_button = new Button();
            $growfund_reset_button->id        = "growfund_forgot_password_submit_button";
            $growfund_reset_button->classname = 'growfund-forgot-password-submit-button growfund-branding-btn';
            $growfund_reset_button->label     = __('Send Reset Link', 'growfund');
            $growfund_reset_button->type      = 'submit';

            growfund_render( $growfund_reset_button );
            ?>
        

        <div class="growfund-forgot-password-footer">
            
                <span class="growfund-forgot-password-login-text">
                    <?php echo esc_html__('Remembered your password?', 'growfund'); ?>
                </span>
                <a href="<?php echo esc_url(growfund_login_url()); ?>" class="growfund-forgot-password-login-link">
                    <?php echo esc_html__('Log in', 'growfund'); ?>
                </a>
           
        </div>

    </form>
</div>
