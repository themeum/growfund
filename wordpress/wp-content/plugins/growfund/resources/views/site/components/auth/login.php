<?php

/** @var Growfund\Views\Components\Auth\Login $login */

use Growfund\Core\AppSettings;
use Growfund\Supports\Utils;
use Growfund\Views\Components\Auth\SignupVerificationMail;
use Growfund\Views\Components\Form\Button;
use Growfund\Views\Components\Form\PasswordField;
use Growfund\Views\Components\Form\TextField;
use Growfund\Views\Components\UI\Badge;
use Growfund\Views\Components\UI\Image;
use Growfund\Views\Components\UI\Modal;

defined( 'ABSPATH' ) || exit;

?>


<div class="growfund-login-main-wrapper">
    <div class="growfund-login-main-wrapper-header">
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
    $growfund_badge->message = growfund_flash_get_message('login_error');

    growfund_render($growfund_badge);
    ?>
    
    <form class="growfund-login-form" method="POST" action="<?php echo esc_url(Utils::get_site_url('growfund/auth/login')); ?>">
        <?php growfund_nonce_field(); ?>
        <?php
            $growfund_email_field = new TextField();
            $growfund_email_field->label       = __('Username/Email address', 'growfund');
            $growfund_email_field->placeholder = __('e.g. johnsmith@yourmail.com', 'growfund');
            $growfund_email_field->name        = 'user_login'; 
            $growfund_email_field->id          = 'growfund_login_email';
            $growfund_email_field->classname   = 'growfund-login-text-field';
            $growfund_email_field->value   = growfund_flash_get_message('login_form_old')['user_login'] ?? '';
            $growfund_email_field->error_msg   = growfund_flash_get_message('login_form_errors')['user_login'][0] ?? '';
            growfund_render( $growfund_email_field );
		?>

        <?php
            $growfund_password_field = new PasswordField();
            $growfund_password_field->label       = __('Password', 'growfund');
            $growfund_password_field->placeholder = '********';
            $growfund_password_field->name        = 'password';
            $growfund_password_field->id          = 'growfund_login_password';
            $growfund_password_field->classname   = 'growfund-login-text-field';
            $growfund_password_field->error_msg   = growfund_flash_get_message('login_form_errors')['password'][0] ?? '';

            growfund_render($growfund_password_field);
		?>
        <a class="growfund-login-forgot-password-link" href="<?php echo esc_url(Utils::get_site_url('auth/forgot-password')); ?>"><?php esc_html_e('Forgot password?', 'growfund'); ?></a>
        <div class="growfund-login-button-wrapper">
            <?php
            $growfund_login_button = new Button();
            $growfund_login_button->id        = "growfund_login_submit_button";
            $growfund_login_button->classname = 'growfund-login-submit-button growfund-branding-btn';
            $growfund_login_button->label     = __('Log in', 'growfund');
            $growfund_login_button->type      = 'submit';
            growfund_render($growfund_login_button);
            ?>
        </div>

        <div class="growfund-login-footer">
            <p class="growfund-login-terms-text">
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

            <div class="growfund-login-prompt">
                <span class="growfund-login-text">
                    <?php echo esc_html__( 'Don\'t have an account?', 'growfund' ); ?>
                </span>
                <a href="<?php echo esc_url(growfund_register_url()); ?>" class="growfund-login-sign-link">
                    <?php echo esc_html__( 'Sign up', 'growfund' ); ?>
                </a>
            </div>
        </div>

    </form>
</div>

<?php 
if ($login->is_verfication_mail_sent) {
    $growfund_signup_verification_mail = new SignupVerificationMail();
    $growfund_signup_verification_mail->email = $login->verification_email;

	$growfund_signup_verification_toaster = new Modal();
	$growfund_signup_verification_toaster->classname = 'is-active';
	$growfund_signup_verification_toaster->id = "growfund-signup-verification-modal";
	$growfund_signup_verification_toaster->show_footer = false;
	$growfund_signup_verification_toaster->show_header = false;
    $growfund_signup_verification_toaster->show_header_icon = true;
    $growfund_signup_verification_toaster->header_icon = 'assets/site/icon/cross.svg';

	$growfund_signup_verification_toaster->body_content = growfund_get_html($growfund_signup_verification_mail);
	growfund_render($growfund_signup_verification_toaster);
}
?>