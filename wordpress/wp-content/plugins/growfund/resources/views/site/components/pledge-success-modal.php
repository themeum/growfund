<?php

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\PaymentEngine;
?>

<div class="growfund-modal is-open">
    <div class="growfund-modal__overlay"></div>
    <div class="growfund-modal__content growfund_contribution_success_modal_container">
        <div class="growfund_icon_container">
            <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M15.9998 2.66602C8.65317 2.66602 2.6665 8.65268 2.6665 15.9993C2.6665 23.346 8.65317 29.3327 15.9998 29.3327C23.3465 29.3327 29.3332 23.346 29.3332 15.9993C29.3332 8.65268 23.3465 2.66602 15.9998 2.66602ZM22.3732 12.9327L14.8132 20.4927C14.6265 20.6793 14.3732 20.786 14.1065 20.786C13.8398 20.786 13.5865 20.6793 13.3998 20.4927L9.6265 16.7193C9.23984 16.3327 9.23984 15.6927 9.6265 15.306C10.0132 14.9193 10.6532 14.9193 11.0398 15.306L14.1065 18.3727L20.9598 11.5193C21.3465 11.1327 21.9865 11.1327 22.3732 11.5193C22.7598 11.906 22.7598 12.5327 22.3732 12.9327Z" fill="#23A26D" />
            </svg>

        </div>
        <h1 class="growfund_title"><?php echo esc_html(!empty($contribution->confirmation_title) ? $contribution->confirmation_title : __('Pledge Successful', 'growfund')); ?></h1>
        <p class="growfund_subtitle"><?php echo esc_html(!empty($contribution->confirmation_description) ? $contribution->confirmation_description : __('Thank you for backing this campaign. Your support brings the campaign closer to life.', 'growfund')); ?></p>

        <div class="growfund_divider"></div>

        <div class="growfund_total_contribution">
            <div class="growfund_total_label"><?php echo esc_html__('Total Pledged Campaign', 'growfund'); ?></div>
            <div class="growfund_total_amount">
                <?php echo esc_html(growfund_to_currency($contribution->amount ?? 0)); ?>
            </div>
        </div>

        <div class="growfund_section_label">
            <svg width="17" height="16" viewBox="0 0 17 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M8.23333 11.2C8.1633 11.4539 8.04394 11.6915 7.88207 11.8993C7.7202 12.107 7.519 12.2809 7.28995 12.4109C7.0609 12.5409 6.80848 12.6245 6.54712 12.6569C6.28575 12.6894 6.02056 12.67 5.76667 12.6C5.51278 12.53 5.27517 12.4106 5.06741 12.2487C4.85965 12.0869 4.68581 11.8857 4.5558 11.6566C4.4258 11.4276 4.34219 11.1752 4.30974 10.9138C4.27728 10.6524 4.29663 10.3872 4.36667 10.1333M2.5 7.33333L14.5 4V12L2.5 9.33333V7.33333Z" stroke="#8C8C8C" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>

            <?php echo esc_html__('Campaign Details', 'growfund'); ?>
        </div>
        <div class="growfund_details_grid">
            <div class="growfund_detail_item">
                <div class="growfund_detail_label"><?php echo esc_html__('Campaign Title', 'growfund'); ?></div>
                <div class="growfund_detail_value"><?php echo esc_html($contribution->campaign_title); ?></div>
            </div>
            <div class="growfund_detail_item">
                <div class="growfund_detail_label"><?php echo esc_html__('Reward Tier', 'growfund'); ?></div>
                <div class="growfund_detail_value"><?php echo esc_html(!empty($contribution->reward_title) ? $contribution->reward_title : 'N/A'); ?></div>
            </div>
            <div class="growfund_detail_item">
                <div class="growfund_detail_label"><?php echo esc_html__('Ref Number', 'growfund'); ?></div>
                <div class="growfund_detail_value"><?php echo esc_html($contribution->ref_number); ?></div>
            </div>
            <div class="growfund_detail_item">
                <div class="growfund_detail_label"><?php echo esc_html__('Payment Method', 'growfund'); ?></div>
                <div class="growfund_detail_value"><?php echo esc_html($contribution->payment_method ?? '___'); ?></div>
            </div>
        </div>

        <div class="growfund_section_label">
            <svg width="17" height="16" viewBox="0 0 17 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M13.1668 14V12.6667C13.1668 11.9594 12.8859 11.2811 12.3858 10.781C11.8857 10.281 11.2074 10 10.5002 10H6.50016C5.79292 10 5.11464 10.281 4.61454 10.781C4.11445 11.2811 3.8335 11.9594 3.8335 12.6667V14M11.1668 4.66667C11.1668 6.13943 9.97292 7.33333 8.50016 7.33333C7.0274 7.33333 5.8335 6.13943 5.8335 4.66667C5.8335 3.19391 7.0274 2 8.50016 2C9.97292 2 11.1668 3.19391 11.1668 4.66667Z" stroke="#8C8C8C" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            <?php echo esc_html(__('Backer Details', 'growfund')); ?>
        </div>
        <div class="growfund_details_grid">
            <div class="growfund_detail_item">
                <div class="growfund_detail_label"><?php echo esc_html__('Backer Name', 'growfund'); ?></div>
                <div class="growfund_detail_value"><?php echo esc_html($contribution->contributor_name); ?></div>
            </div>
            <div class="growfund_detail_item">
                <div class="growfund_detail_label"><?php echo esc_html__('Email Address', 'growfund'); ?></div>
                <div class="growfund_detail_value"><?php echo esc_html($contribution->contributor_email); ?></div>
            </div>
        </div>

        <?php if (growfund_payment_engine() === PaymentEngine::NATIVE && $contribution->is_future_payment) : ?>
            <div class="growfund_contribution_info">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M13.3332 4L5.99984 11.3333L2.6665 8" stroke="#338C58" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>

                <?php echo esc_html__("You'll be charged only if the campaign is successfully funded.", 'growfund'); ?>
            </div>
        <?php endif; ?>

        <a href="<?php echo esc_url(growfund_user_dashboard_url()); ?>" class="growfund_back_button">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z" />
            </svg>
            <?php echo is_user_logged_in() ? esc_html__('Back to Dashboard', 'growfund') : esc_html__('Back to Site', 'growfund'); ?>
        </a>
    </div>
</div>