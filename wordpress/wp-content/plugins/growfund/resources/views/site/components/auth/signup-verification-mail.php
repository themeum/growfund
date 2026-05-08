<?php
/**
 * @var Growfund\Views\Components\Auth\SignupVerificationMail $signup_verification_mail
 */

use Growfund\Views\Components\Form\Button;

defined('ABSPATH') || exit;
?>

<div class="growfund-signup-verification-mail-card">

    <div class="growfund-signup-verification-mail-header">
        <h2 class="growfund-signup-verification-mail-title">
            <?php echo esc_html__('Check Your Inbox', 'growfund'); ?>
        </h2>
        <span class="growfund-signup-verification-mail-close-button-icon growfund-modal-close-button-icon">
            <?php growfund_echo_safe_html($signup_verification_mail->get_svg_icon('assets/site/icon/cross.svg')); ?>
        </span>
    </div>

    <div class="growfund-signup-verification-mail-content">
        <p class="growfund-signup-verification-mail-description">
            <?php
            $growfund_verification_email = $signup_verification_mail->email ? $signup_verification_mail->email : __('your email', 'growfund');

            printf(
                /* translators: %s: user email */
                esc_html__('We’ve sent a verification email to %s.', 'growfund'),
                wp_kses_post('<strong>' . $growfund_verification_email . '</strong>')
            );
            ?>
            <br>
            <?php echo esc_html__('Please check your email, including the spam folder.', 'growfund'); ?>
        </p>
    </div>

    <div class="growfund-signup-verification-mail-footer">
        <?php
        $growfund_login_button = new Button();
        $growfund_login_button->classname = 'growfund-signup-verification-mail-button growfund-branding-btn';
        $growfund_login_button->id = "growfund-signup-verification-mail-button";
        $growfund_login_button->label = __('Okay', 'growfund');

        growfund_render($growfund_login_button);
        ?>
    </div>

</div>