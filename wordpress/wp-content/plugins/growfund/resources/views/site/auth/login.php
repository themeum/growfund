<?php

defined( 'ABSPATH' ) || exit;

/**
 * Login Template
 * 
 * @var string $redirect_to
 * @var string $is_shortcode
 * @var string $error
 * @var string $success
 */

// Get login form fields
$login_fields = [
    [
        'type' => 'input',
        'name' => 'email',
        'label' => __('Email Address', 'growfund'),
        'data' => [
            'name' => 'email',
            'type' => 'email',
            'placeholder' => __('Enter your email', 'growfund'),
            'required' => true,
            'value' => '',
            'autocomplete' => 'email'
        ]
    ],
    [
        'type' => 'input',
        'name' => 'password',
        'label' => __('Password', 'growfund'),
        'data' => [
            'name' => 'password',
            'type' => 'password',
            'placeholder' => __('Enter your password', 'growfund'),
            'required' => true,
            'value' => '',
        ]
    ],
    [
        'type' => 'html',
        'content' => '<div class="growfund-forgot-password-link"><a href="' . esc_url(growfund_forget_password_url()) . '" class="growfund-login-link growfund-login-link--bold">' . __('Forgot your password?', 'growfund') . '</a></div>'
    ],
    [
        'type' => 'input',
        'data' => [
            'type' => 'hidden',
            'name' => 'redirect_to',
            'value' => esc_attr($redirect_to)
        ]
    ]
];

$error = $error ?? growfund_flash_get_message('error') ?? null; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
$success = $success ?? growfund_flash_get_message('success') ?? null;

// Load WordPress header to include CSS and JS
if (!$is_shortcode) {
    growfund_get_header();
}
?>

<div class="growfund-login-container growfund-page-container">
    <div class="growfund-login-card">
        <div class="growfund-login-header">
            <?php
            growfund_renderer()->render('site.components.image', [
				'src' => growfund_site_image_url('logo.svg'),
				'alt' => 'Growfund',
				'attributes' => [
					'class' => 'growfund-login-logo'
				]
			]);
            ?>
        </div>
        <?php
        if (!empty($error)) :
			?>
            <div class="growfund-alert growfund-alert--error">
                <p><?php echo esc_html(urldecode($error)); ?></p>
            </div>
			<?php
        endif;
        if (!empty($success)) :
			?>
            <div class="growfund-alert growfund-alert--success">
                <p><?php echo esc_html($success); ?></p>
            </div>
        <?php endif; ?>

        <?php
        growfund_renderer()->render('site.components.form-builder', [
            'fields' => $login_fields,
            'form_attributes' => [
                'method' => 'POST',
                'action' => '',
                'class' => 'growfund-login-form'
            ],
            'submit_button_text' => __('Sign In', 'growfund'),
            'submit_button_attributes' => [
                'class' => 'growfund-login-btn growfund-login-btn--primary growfund-login-btn--full'
            ]
        ]);
        ?>

        <div class="growfund-login-footer">
            <p class="growfund-login-terms-text">
                <?php esc_html_e('By continue, you agree to the', 'growfund'); ?>
                <a href="<?php echo esc_url(home_url('/terms/')); ?>" class="growfund-login-link growfund-login-link--bold"><?php esc_html_e('Terms and Conditions', 'growfund'); ?></a>
                <?php esc_html_e('and', 'growfund'); ?>
                <a href="<?php echo esc_url(home_url('/privacy/')); ?>" class="growfund-login-link growfund-login-link--bold"><?php esc_html_e('Privacy Policy', 'growfund'); ?></a>
                <?php esc_html_e('of Growfund.', 'growfund'); ?>
            </p>
            <p class="growfund-login-links">
                <?php esc_html_e('Have an account?', 'growfund'); ?>
                <a href="<?php echo esc_url(growfund_register_url()); ?>" class="growfund-login-link growfund-login-link--bold">
                    <?php esc_html_e('Sign up', 'growfund'); ?>
                </a>
            </p>
        </div>
    </div>
</div>

<?php
if (!$is_shortcode) {
    growfund_get_footer();
}
?>