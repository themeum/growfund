<?php

use Growfund\Core\AppSettings;
use Growfund\Sanitizer;

defined( 'ABSPATH' ) || exit;

/**
 * Register Template
 * 
 * @var string $redirect_to
 * @var string $is_shortcode
 * @var string $error
 * @var string $success
 * @var bool $is_fundraiser
 */

// Load WordPress header to include CSS and JS
if (!$is_shortcode) {
    growfund_get_header();
}

$register_fields = [
    [
        'type' => 'group',
        'wrapper_class' => 'growfund-form-row-half',
        'fields' => [
            [
                'type' => 'input',
                'name' => 'first_name',
                'label' => __('First name', 'growfund'),
                'data' => [
                    'name' => 'first_name',
                    'type' => 'text',
                    'placeholder' => __('e.g. John', 'growfund'),
                    'required' => true,
                    'value' => Sanitizer::apply_rule(wp_unslash(growfund_input_post('first_name', '')), Sanitizer::TEXT),

                ]
            ],
            [
                'type' => 'input',
                'name' => 'last_name',
                'label' => __('Last name', 'growfund'),
                'data' => [
                    'name' => 'last_name',
                    'type' => 'text',
                    'placeholder' => __('e.g. Smith', 'growfund'),
                    'required' => true,
                    'value' => Sanitizer::apply_rule(wp_unslash(growfund_input_post('last_name', '')), Sanitizer::TEXT),
                ]
            ]
        ]
    ],
    [
        'type' => 'input',
        'name' => 'email',
        'label' => __('Email address', 'growfund'),
        'data' => [
            'name' => 'email',
            'type' => 'email',
            'placeholder' => __('e.g. johnsmith@yourmail.com', 'growfund'),
            'required' => true,
            'value' => Sanitizer::apply_rule(wp_unslash(growfund_input_post('email', '')), Sanitizer::EMAIL),
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
            'placeholder' => __('••••••••', 'growfund'),
            'required' => true,
            'value' => Sanitizer::apply_rule(wp_unslash(growfund_input_post('password', '')), Sanitizer::TEXT),
            'class' => 'growfund-input growfund-password-input',
            'autocomplete' => 'new-password',
        ]
    ],
    [
        'type' => 'input',
        'name' => 'password_confirmation',
        'label' => __('Confirm Password', 'growfund'),
        'data' => [
            'name' => 'password_confirmation',
            'type' => 'password',
            'placeholder' => __('••••••••', 'growfund'),
            'required' => true,
            'value' => Sanitizer::apply_rule(wp_unslash(growfund_input_post('password_confirmation', '')), Sanitizer::TEXT),
            'class' => 'growfund-input growfund-password-input',
            'autocomplete' => 'new-password',
        ]
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
?>

<div class="growfund-register-container growfund-page-container">
    <div class="growfund-register-card">
        <div class="growfund-register-header">
            <?php
            $branding_logo_url = growfund_settings(AppSettings::BRANDING)->get_logo('url');
            $branding_logo_height = growfund_settings(AppSettings::BRANDING)->get_logo_height();
            
            growfund_renderer()->render('site.components.image', [
				'src' => !empty($branding_logo_url) ? $branding_logo_url : growfund_site_image_url('logo.svg'),
				'alt' => !empty($branding_logo_url) ? growfund_site_name() : 'Growfund',
				'attributes' => [
					'class' => 'growfund-register-logo',
                    'style' =>  $branding_logo_height ? 'height: ' . $branding_logo_height . 'px' : ''
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
            'fields' => $register_fields,
            'form_attributes' => [
                'method' => 'POST',
                'action' => '',
                'class' => 'growfund-register-form'
            ],
            'submit_button_text' => isset($is_fundraiser) && $is_fundraiser ? esc_html__('Create Fundraiser Account', 'growfund') : esc_html__('Sign up', 'growfund'),
            'submit_button_attributes' => [
                'class' => 'growfund-register-btn growfund-register-btn--primary growfund-register-btn--full growfund-branding-btn'
            ]
        ]);
        ?>

        <div class="growfund-register-footer">
            <p class="growfund-register-terms-text">
                <?php esc_html_e('By continue, you agree to the', 'growfund'); ?>
                <a href="<?php echo esc_url(isset($terms_and_conditions_url) ? $terms_and_conditions_url : home_url('/terms/')); ?>" class="growfund-register-link growfund-register-link--bold"><?php esc_html_e('Terms and Conditions', 'growfund'); ?></a>
                <?php esc_html_e('and', 'growfund'); ?>
                <a href="<?php echo esc_url(isset($privacy_url) ? $privacy_url : home_url('/privacy/')); ?>" class="growfund-register-link growfund-register-link--bold"><?php esc_html_e('Privacy Policy', 'growfund'); ?></a>
                <?php esc_html_e('of ', 'growfund') ; ?> <?php echo esc_html(growfund_site_name()); ?>.
            </p>
            <p class="growfund-register-links">
                <?php esc_html_e('Have an account?', 'growfund'); ?>
                <a href="<?php echo esc_url(growfund_login_url()); ?>" class="growfund-register-link growfund-register-link--bold">
                    <?php esc_html_e('Log in', 'growfund'); ?>
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