<?php

/** @var Growfund\Views\Components\Auth\ResetLink $reset_link */

use Growfund\Core\AppSettings;
use Growfund\Supports\Utils;
use Growfund\Views\Components\UI\Image;

defined( 'ABSPATH' ) || exit;

?>


<div class="growfund-reset-link-main-wrapper">
    <div class="growfund-reset-link-main-wrapper-header">
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
    
    <div class="growfund-reset-link-content">
        <h2 class="growfund-reset-link-title">
            <?php echo esc_html__('Reset Link Sent', 'growfund'); ?>
        </h2>
        <p class="growfund-reset-link-description">
            <?php 
            echo esc_html__( 'We’ve sent a password reset link to ', 'growfund' ); 
            ?>
            <strong class="growfund-reset-link-email">
                <?php echo esc_html($reset_link->user_email) . '.'; ?>
            </strong>
            </br>
            <?php 
            echo esc_html__('if you have an existing account. Check your email and click the link to reset your password.', 'growfund'); 
            ?>
        </p>
    </div>

    <div class="growfund-reset-link-footer">
        <div class="growfund-reset-link-prompt">
            <span class="growfund-reset-link-prompt-text">
                <?php echo esc_html__('Don’t get a link?', 'growfund'); ?>
            </span>
            <form class="growfund-forgot-password-form" method="POST" action="<?php echo esc_url(Utils::get_site_url('growfund/auth/forgot-password')); ?>">
                <?php growfund_nonce_field(); ?>
                <input type="hidden" name="email" value="<?php echo esc_attr($reset_link->user_email); ?>" />
                <button type="submit" class="growfund-reset-link-resend-link growfund-branding-btn">
                    <?php echo esc_html__('Send Again', 'growfund'); ?>
                </button>
            </form>
        </div>
    </div>
</div>
