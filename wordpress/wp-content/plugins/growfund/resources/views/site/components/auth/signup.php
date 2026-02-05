
<?php

/** @var Growfund\Views\Components\Auth\Signup $signup */

use Growfund\Core\AppSettings;
use Growfund\Supports\Utils;
use Growfund\Views\Components\Form\Button;
use Growfund\Views\Components\Form\PasswordField;
use Growfund\Views\Components\Form\TextField;
use Growfund\Views\Components\UI\Badge;
use Growfund\Views\Components\UI\Image;

defined( 'ABSPATH' ) || exit;


?>


<div class="growfund-signup-main-wrapper">
    <div class="growfund-signup-main-wrapper-header">
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

    <?php 
    $growfund_badge = new Badge();
    $growfund_badge->variant = 'error';
    $growfund_badge->message = growfund_flash_get_message('registration_error');

    growfund_render($growfund_badge);
    ?>

    <form class="growfund-signup-form" method="POST" action="<?php echo esc_url(Utils::get_site_url('growfund/auth/register')); ?>">

        <?php growfund_nonce_field(); ?>

        <input type="hidden" name="user_type" value="<?php echo esc_attr($signup->user_type); ?>" />
        
        <div class="growfund-signup-name-row">
            <?php
            $growfund_first_name_field = new TextField();
            $growfund_first_name_field->label = __('First name', 'growfund');
            $growfund_first_name_field->placeholder = __('e.g. John', 'growfund');
            $growfund_first_name_field->name = 'first_name';
            $growfund_first_name_field->id = 'growfund_signup_first_name';
            $growfund_first_name_field->classname = 'growfund-signup-text-field';
            $growfund_first_name_field->value   = growfund_flash_get_message('registration_form_old')['first_name'] ?? '';
            $growfund_first_name_field->error_msg   = growfund_flash_get_message('registration_form_errors')['first_name'][0] ?? '';
            growfund_render($growfund_first_name_field);
            ?>
        
        
            <?php
            $growfund_last_name_field = new TextField();
            $growfund_last_name_field->label = __('Last name', 'growfund');
            $growfund_last_name_field->placeholder = __('e.g. Smith', 'growfund');
            $growfund_last_name_field->name = 'last_name';
            $growfund_last_name_field->id = 'growfund_signup_last_name';
            $growfund_last_name_field->classname = 'growfund-signup-text-field';
            $growfund_last_name_field->value = growfund_flash_get_message('registration_form_old')['last_name'] ?? '';
            $growfund_last_name_field->error_msg   = growfund_flash_get_message('registration_form_errors')['last_name'][0] ?? '';
            growfund_render($growfund_last_name_field);
            ?>
        </div>
        <?php
            $growfund_email_field = new TextField();
            $growfund_email_field->label = __('Email address', 'growfund');
            $growfund_email_field->placeholder = __('e.g. johnsmith@yourmail.com', 'growfund');
            $growfund_email_field->name = 'email';
            $growfund_email_field->id = 'growfund_signup_email';
            $growfund_email_field->classname = 'growfund-signup-text-field';
            $growfund_email_field->value = growfund_flash_get_message('registration_form_old')['email'] ?? '';
            $growfund_email_field->error_msg   = growfund_flash_get_message('registration_form_errors')['email'][0] ?? '';
            growfund_render($growfund_email_field);
        ?>

        <?php
            $growfund_username_field = new TextField();
            $growfund_username_field->label = __('Username', 'growfund');
            $growfund_username_field->placeholder = __('e.g. johnsmith', 'growfund');
            $growfund_username_field->name = 'username';
            $growfund_username_field->id = 'growfund_signup_username';
            $growfund_username_field->classname = 'growfund-signup-text-field';
            $growfund_username_field->value = growfund_flash_get_message('registration_form_old')['username'] ?? '';
            $growfund_username_field->error_msg   = growfund_flash_get_message('registration_form_errors')['username'][0] ?? '';
            growfund_render($growfund_username_field);
        ?>
       
        <?php
            $growfund_password_field = new PasswordField();
            $growfund_password_field->label = __('Password', 'growfund');
            $growfund_password_field->placeholder = '******';
            $growfund_password_field->name = 'password';
            $growfund_password_field->id = 'growfund_signup_password';
            $growfund_password_field->classname = 'growfund-signup-password-field';
            $growfund_password_field->error_msg   = growfund_flash_get_message('registration_form_errors')['password'][0] ?? '';
            growfund_render($growfund_password_field);
        ?>

        <?php
            $growfund_password_confirm_field = new PasswordField();
            $growfund_password_confirm_field->label = __('Password', 'growfund');
            $growfund_password_confirm_field->placeholder = '******';
            $growfund_password_confirm_field->name = 'password_confirmation';
            $growfund_password_confirm_field->id = 'growfund_signup_password_confirmation';
            $growfund_password_confirm_field->classname = 'growfund-signup-password-field';
            $growfund_password_confirm_field->error_msg   = growfund_flash_get_message('registration_form_errors')['password_confirmation'][0] ?? '';

            growfund_render($growfund_password_confirm_field);
        ?>
      
        <?php
            $growfund_signup_button = new Button();
            $growfund_signup_button->id = "growfund_signup_submit_button";
            $growfund_signup_button->classname = 'growfund-signup-submit-button growfund-branding-btn';
            $growfund_signup_button->label = __('Sign up', 'growfund');
            $growfund_signup_button->type = 'submit';
            growfund_render($growfund_signup_button);
        ?>
        <div class="growfund-signup-footer">
            <p class="growfund-signup-terms-text">
                <?php
                
                printf(
                    /* translators: 1: Terms and Conditions, 2: Privacy Policy, 3: site name */
                    esc_html__('By continue, you agree to the %1$s and %2$s of %3$s', 'growfund'), 
                    '<a href="' . esc_url(growfund_terms_and_conditions_url()) . '" class="growfund-signup-link">' . esc_html__('Terms and Conditions', 'growfund') . '</a>',
                    '<a href="' . esc_url(growfund_privacy_policy_url()) . '" class="growfund-signup-link">' . esc_html__('Privacy Policy', 'growfund') . '</a>',
                    esc_html(growfund_site_name())
				);
                ?>
            </p>

            <div class="growfund-signup-login-prompt">
                <span class="growfund-signup-login-text">
                    <?php echo esc_html__( 'Have an account?', 'growfund' ); ?>
                </span>
                <a href="<?php echo esc_url(growfund_login_url()); ?>" class="growfund-signup-login-link">
                    <?php echo esc_html__( 'Log in', 'growfund' ); ?>
                </a>
            </div>
        </div>

    </form>
</div>
