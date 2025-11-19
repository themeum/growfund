<?php

defined( 'ABSPATH' ) || exit;

/**
 * Donation List Component
 * Displays a list of recent donations with donor information and labels
 * 
 * @param array $donations - Array of donation objects
 * @param int $max_items - Maximum number of items to display
 */

// Set default values for variables
$donations = $donations ?? [];
$max_items = $max_items ?? 3;
$campaign_id = $campaign_id ?? 0;

// Filter donations to max items
$display_donations = array_slice($donations, 0, $max_items);
?>

<div class="growfund-donation-list">
    <!-- Donation Items -->
    <div class="growfund-donation-list__items">
        <?php if (!empty($display_donations)) : ?>
            <?php foreach ($display_donations as $donation) : ?>
                <?php
                $donor_name = esc_html__('Anonymous', 'growfund');

                if (!$donation->is_anonymous && $donation->donor) {
                    $first_name = $donation->donor->first_name ?? '';
                    $last_name = $donation->donor->last_name ?? '';
                    $donor_name = trim($first_name . ' ' . $last_name);
                    if (empty($donor_name)) {
                        $donor_name = esc_html__('Anonymous', 'growfund');
                    }
                }

                growfund_renderer()
                    ->render('site.components.donation-item', [
                        'donor_name' => esc_html($donor_name),
                        'amount' => $donation->amount ?? 0,
                        'timestamp' => esc_html(strtotime($donation->created_at ?? 'now')),
                        'currency' => 'USD'
                    ]);
                ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Navigation Buttons -->
    <?php if (!empty($donations)) : ?>
        <div class="growfund-donation-list__navigation">
            <?php
            growfund_renderer()
                ->render('site.components.button', [
                    'text' => esc_html__('See all', 'growfund'),
                    'variant' => 'default',
                    'class' => 'growfund-donation-list__nav-btn growfund-donation-list__nav-btn--all growfund-btn--gray',
                    'attributes' => [
                        'data-modal-trigger' => 'growfund-donation-modal-' . esc_attr($campaign_id),
                        'data-sort' => 'newest'
                    ]
                ]);
            ?>
            <?php
            growfund_renderer()
                ->render('site.components.button', [
                    'text' => esc_html__('See top', 'growfund'),
                    'variant' => 'default',
                    'class' => 'growfund-donation-list__nav-btn growfund-donation-list__nav-btn--top growfund-btn--gray',
                    'icon' => 'star',
                    'iconPosition' => 'left',
                    'attributes' => [
                        'data-modal-trigger' => 'growfund-donation-modal-' . esc_attr($campaign_id),
                        'data-sort' => 'top'
                    ]
                ]);
            ?>
        </div>
    <?php endif; ?>
</div>