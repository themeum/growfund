<?php

defined( 'ABSPATH' ) || exit;

/**
 * Donation Modal List Component
 * Displays a list of donations for the modal with infinite scroll support
 * 
 * @param array $donations - Array of donation objects
 * @param object $pagination - Pagination data object
 * @param string $sort - Current sort option ('newest' or 'top')
 */

// Set default values for variables
$donations = $donations ?? [];
$pagination = $pagination ?? null;
$sort = $sort ?? 'newest';
?>

<div class="growfund-donation-modal-list">
    <!-- Donation Items -->
    <div class="growfund-donation-modal-list__items">
        <?php if (empty($donations)) : ?>
            <div class="growfund-donation-modal-list__empty">
                <p><?php esc_html_e('No donations found', 'growfund'); ?></p>
            </div>
        <?php else : ?>
            <?php foreach ($donations as $donation) : ?>
                <?php
                $donor_name = __('Anonymous', 'growfund');

                if (!$donation->is_anonymous && $donation->donor) {
                    $first_name = $donation->donor->first_name ?? '';
                    $last_name = $donation->donor->last_name ?? '';
                    $donor_name = trim($first_name . ' ' . $last_name);
                    if (empty($donor_name)) {
                        $donor_name = __('Anonymous', 'growfund');
                    }
                }

                growfund_renderer()
                    ->render('site.components.donation-item', [
                        'donor_name' => $donor_name,
                        'amount' => $donation->amount ?? 0,
                        'timestamp' => strtotime($donation->created_at ?? 'now'),
                        'currency' => 'USD',
                        'variant' => 'modal'
                    ]);
                ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>