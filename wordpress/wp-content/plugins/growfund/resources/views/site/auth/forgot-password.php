<?php

use Growfund\Core\AppSettings;

defined( 'ABSPATH' ) || exit;

/**
 * Forgot Password Template
 * 
 * @var string $error
 * @var string $success
 * @var string $submitted_email
 */

// Load WordPress header to include CSS and JS
growfund_get_header();

$forget_password_fields = [
    [
        'type' => 'input',
        'name' => 'email',
        'label' => esc_html__('Email Address', 'growfund'),
        'data' => [
            'name' => 'email',
            'type' => 'email',
            'placeholder' => esc_html__('Enter your email', 'growfund'),
            'required' => true,
            'value' => '',
            'autocomplete' => 'email'
        ]
    ]
];
?>

<div class="growfund-forgot-password-container growfund-page-container">
    <div class="growfund-forgot-password-card">
        <div class="growfund-forgot-password-header">
            <?php
            $branding_logo_url = growfund_settings(AppSettings::BRANDING)->get_logo('url');
            $branding_logo_height = growfund_settings(AppSettings::BRANDING)->get_logo_height();

            growfund_renderer()->render('site.components.image', [
				'src' => !empty($branding_logo_url) ? $branding_logo_url : growfund_site_image_url('logo.svg'),
				'alt' => !empty($branding_logo_url) ? growfund_site_name() : 'Growfund',
				'attributes' => [
					'class' => 'growfund-forgot-password-logo',
                    'style' =>  $branding_logo_height ? 'height: ' . $branding_logo_height . 'px' : ''
				]
			]);
            ?>

            <?php if (!empty($error)) : ?>
                <div class="growfund-alert growfund-alert--error">
                    <p><?php echo esc_html($error); ?></p>
                </div>
            <?php endif; ?>

            <?php if (!empty($submitted_email)) : ?>
                <p class="growfund-forgot-password-subtitle">
                    <?php
                    printf(
                        /* translators: %s: email address */
                        esc_html__('We\'ve sent a password reset link to %s if you have an existing account. Check your email and click the link to reset your password.', 'growfund'),
                        '<strong>' . esc_html($submitted_email) . '</strong>'
                    );
                    ?>
                </p>
            <?php else : ?>
                <p class="growfund-forgot-password-subtitle"><?php esc_html_e('No worries! Enter your email address and we\'ll send you a link to reset your password.', 'growfund'); ?></p>
            <?php endif; ?>
        </div>



        <?php if (empty($submitted_email)) : ?>
            <?php

            growfund_renderer()->render('site.components.form-builder', [
                'fields' => $forget_password_fields,
                'form_attributes' => [
                    'method' => 'POST',
                    'action' => '',
                    'class' => 'growfund-forgot-password-form'
                ],
                'submit_button_text' => esc_html__('Send Reset Link', 'growfund'),
                'submit_button_attributes' => [
                    'class' => 'growfund-forgot-password-btn growfund-forgot-password-btn--primary growfund-forgot-password-btn--full'
                ]
            ]);
            ?>
        <?php endif; ?>

        <div class="growfund-forgot-password-footer">
            <?php if (!empty($submitted_email)) : ?>
                <p class="growfund-forgot-password-links">
                    <?php esc_html_e('Don\'t get a link?', 'growfund'); ?>
                    <a href="<?php echo esc_url(growfund_forget_password_url()); ?>" class="growfund-forgot-password-link growfund-forgot-password-link--bold">
                        <?php esc_html_e('Send Again', 'growfund'); ?>
                    </a>
                </p>
            <?php else : ?>
                <p class="growfund-forgot-password-links">
                    <?php esc_html_e('Remember your password?', 'growfund'); ?>
                    <a href="<?php echo esc_url(growfund_login_url()); ?>" class="growfund-forgot-password-link growfund-forgot-password-link--bold">
                        <?php esc_html_e('Log in', 'growfund'); ?>
                    </a>
                </p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php growfund_get_footer(); ?>