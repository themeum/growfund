<?php

defined( 'ABSPATH' ) || exit;

/**
 * Reset Password Template
 * 
 * @var string $error
 * @var string $success
 * @var string $key
 * @var string $login
 */

// Load WordPress header to include CSS and JS
growfund_get_header();


$password_reset_fields = [
    [
        'type' => 'input',
        'name' => 'password',
        'label' => __('New Password', 'growfund'),
        'data' => [
            'name' => 'password',
            'type' => 'password',
            'placeholder' => __('Enter your new password', 'growfund'),
            'required' => true,
            'value' => '',
            'autocomplete' => 'new-password',
            'spellcheck' => 'false'
        ]
    ],
    [
        'type' => 'input',
        'name' => 'password_confirmation',
        'label' => __('Confirm New Password', 'growfund'),
        'data' => [
            'name' => 'password_confirmation',
            'type' => 'password',
            'placeholder' => __('Confirm your new password', 'growfund'),
            'required' => true,
            'value' => '',
            'autocomplete' => 'new-password',
            'spellcheck' => 'false'
        ]
    ],
    [
        'type' => 'input',
        'data' => [
            'type' => 'hidden',
            'name' => 'key',
            'value' => ''
        ]
    ],
    [
        'type' => 'input',
        'data' => [
            'type' => 'hidden',
            'name' => 'login',
            'value' => ''
        ]
    ]
];
?>

<div class="growfund-reset-password-container growfund-page-container">
    <div class="growfund-reset-password-card">
        <div class="growfund-reset-password-header">
            <?php
            growfund_renderer()->render('site.components.image', [
				'src' => growfund_site_image_url('logo.svg'),
				'alt' => 'Growfund',
				'attributes' => [
					'class' => 'growfund-reset-password-logo'
				]
			]);
            ?>
            <h1 class="growfund-reset-password-title"><?php esc_html_e('Reset Password', 'growfund'); ?></h1>
            <p class="growfund-reset-password-subtitle"><?php esc_html_e('Enter your new password below.', 'growfund'); ?></p>
        </div>

        <?php if (!empty($error)) : ?>
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

        <?php if (isset($show_form) && $show_form === false) : ?>
            <!-- Form is hidden due to invalid link -->
        <?php else : ?>
            <?php

            growfund_renderer()->render('site.components.form-builder', [
                'fields' => $password_reset_fields,
                'form_attributes' => [
                    'method' => 'POST',
                    'action' => '',
                    'class' => 'growfund-reset-password-form'
                ],
                'submit_button_text' => __('Reset Password', 'growfund'),
                'submit_button_attributes' => [
                    'class' => 'growfund-reset-password-btn growfund-reset-password-btn--primary growfund-reset-password-btn--full'
                ]
            ]);
            ?>
        <?php endif; ?>

        <div class="growfund-reset-password-footer">
            <p class="growfund-reset-password-links">
                <?php esc_html_e('Remember your password?', 'growfund'); ?>
                <a href="<?php echo esc_url(growfund_login_url()); ?>" class="growfund-reset-password-link growfund-reset-password-link--bold">
                    <?php esc_html_e('Log in', 'growfund'); ?>
                </a>
            </p>
        </div>
    </div>
</div>

<?php growfund_get_footer(); ?>