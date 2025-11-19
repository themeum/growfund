<?php

defined( 'ABSPATH' ) || exit;

/**
 * Donation Modal Component
 * Displays a modal with all donations, sorting options, and infinite scroll
 * 
 * @param int $campaign_id - Campaign ID to fetch donations for
 * @param int $total_donations - Total number of donations
 */

use Growfund\Supports\Utils;

$campaign_id = $campaign_id ?? 0;
$total_donations = $total_donations ?? 0;

$is_user_logged_in = growfund_user()->is_logged_in();

$checkout_url = $campaign->checkout_url ?? Utils::get_checkout_url($campaign_id);
?>

<div class="growfund-donation-modal" id="growfund-donation-modal-<?php echo esc_attr($campaign_id); ?>" data-checkout-url="<?php echo esc_url($checkout_url); ?>">
    <div class="growfund-donation-modal__overlay"></div>

    <div class="growfund-donation-modal__content">
        <!-- Modal Header -->
        <div class="growfund-donation-modal__header">
            <h3 class="growfund-donation-modal__title">
                <?php
                /* Translators: %d: Total number of donations */
                printf(esc_html__('Donations (%d)', 'growfund'), esc_html($total_donations));
                ?>
            </h3>
            <?php
            growfund_renderer()
                ->render('site.components.button', [
                    'text' => '',
                    'type' => 'button',
                    'variant' => 'icon-only',
                    'class' => 'growfund-donation-modal__close',
                    'icon' => 'cross',
                    'ariaLabel' => esc_html__('Close modal', 'growfund')
                ]);
            ?>
        </div>

        <!-- Sorting Options -->
        <div class="growfund-donation-modal__sorting">
            <?php
            growfund_renderer()
                ->render('site.components.button', [
                    'text' => esc_html__('Newest', 'growfund'),
                    'type' => 'button',
                    'variant' => 'default',
                    'class' => 'growfund-donation-modal__sort-btn growfund-donation-modal__sort-btn--newest growfund-donation-modal__sort-btn--active',
                    'attributes' => [
                        'data-sort' => 'newest',
                        'data-campaign-id' => $campaign_id
                    ]
                ]);
            ?>
            <?php
            growfund_renderer()
                ->render('site.components.button', [
                    'text' => esc_html__('Top', 'growfund'),
                    'type' => 'button',
                    'variant' => 'default',
                    'class' => 'growfund-donation-modal__sort-btn growfund-donation-modal__sort-btn--top',
                    'attributes' => [
                        'data-sort' => 'top',
                        'data-campaign-id' => $campaign_id
                    ]
                ]);
            ?>
        </div>

        <!-- Donations List Container -->
        <div class="growfund-donation-modal__list-container">
            <div class="growfund-donation-modal__list" id="growfund-donation-modal-list-<?php echo esc_attr($campaign_id); ?>">
                <!-- Donations will be loaded here via AJAX -->
            </div>

            <!-- Loading Indicator -->
            <div class="growfund-donation-modal__loading" id="growfund-donation-modal-loading-<?php echo esc_attr($campaign_id); ?>" style="display: none;">
                <div class="growfund-donation-modal__loading-spinner"></div>
                <span><?php esc_html_e('Loading...', 'growfund'); ?></span>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="growfund-donation-modal__footer">
            <?php
            growfund_renderer()
                ->render('site.components.button', [
                    'text' => esc_html__('Donate now', 'growfund'),
                    'type' => 'button',
                    'class' => 'growfund-donation-modal__donate-btn growfund-btn growfund-btn--primary'
                ]);
            ?>
        </div>
    </div>
</div>

<!-- Donation Item Template (hidden, used for cloning) -->
<template id="growfund-donation-item-template">
    <?php
    growfund_renderer()
        ->render('site.components.donation-item', [
            'donor_name' => '',
            'amount' => 0,
            'timestamp' => time(),
            'currency' => Utils::get_currency(),
            'variant' => 'modal'
        ]);
	?>
</template>

<!-- Empty State Template -->
<template id="growfund-donation-empty-template">
    <div class="growfund-donation-modal__empty">
        <p><?php esc_html_e('No donations found', 'growfund'); ?></p>
    </div>
</template>