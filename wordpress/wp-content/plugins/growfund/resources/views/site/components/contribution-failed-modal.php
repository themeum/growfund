<?php defined( 'ABSPATH' ) || exit; ?>

<div class="growfund-modal is-open">
    <div class="growfund-modal__overlay"></div>
    <div class="growfund-modal__content growfund_contribution_failed_modal_container">
        <div class="growfund_icon_container">
            <svg width="26" height="26" viewBox="0 0 26 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect width="26" height="26" rx="13" fill="#D40000" />
                <path d="M16.6289 8.36328C16.9074 8.0847 17.3592 8.0847 17.6377 8.36328C17.9159 8.64189 17.9162 9.09364 17.6377 9.37207L14.0078 13L17.6377 16.6299C17.9159 16.9085 17.9162 17.3602 17.6377 17.6387C17.3593 17.9171 16.9075 17.9169 16.6289 17.6387L12.999 14.0088L9.37109 17.6387C9.09264 17.917 8.64091 17.9168 8.3623 17.6387C8.08374 17.36 8.08374 16.9085 8.3623 16.6299L11.9902 13L8.3623 9.37207C8.08373 9.0935 8.08373 8.64185 8.3623 8.36328C8.64088 8.08471 9.09251 8.08471 9.37109 8.36328L12.999 11.9912L16.6289 8.36328Z" fill="#F5F5F5" stroke="#F5F5F5" stroke-width="0.2" />
            </svg>

        </div>
        <h1 class="growfund_title"><?php echo growfund_app()->is_donation_mode() ? esc_html__('Failed to Donate', 'growfund') : esc_html__('Failed to Pledge', 'growfund'); ?></h1>
        <p class="growfund_subtitle"><?php echo esc_html__('Unfortunately, your payment could not be processed. Please try again', 'growfund'); ?></p>

        <a href="<?php echo esc_url(site_url()); ?>" class="growfund_back_button">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z" />
            </svg>
            <?php echo esc_html__('Back to Site', 'growfund'); ?>
        </a>
    </div>
</div>