<?php

defined( 'ABSPATH' ) || exit;

/**
 * Order Summary Component
 * @param OrderSummaryDTO $summary - Order summary data
 * @param bool $show_bonus - Whether to show the bonus support field (default: true)
 * @param bool $show_shipping - Whether to show the shipping field (default: true)
 */

use Growfund\DTO\Site\OrderSummaryDTO;

$summary = $summary ?? null;

// Create default summary if none provided
if (!$summary) {
    $summary = new OrderSummaryDTO([
        'bonus_support' => 0,
        'shipping' => 0,
        'reward_price' => 0,
        'total' => 0
    ]);
}

$show_bonus = $show_bonus ?? true;
$show_shipping = $show_shipping ?? true;

// Calculate total as bonus_support + shipping + reward_price
$bonus = $show_bonus ? floatval($summary->bonus_support ?? 0) : 0; // Set bonus to 0 when hidden
$shipping = $show_shipping ? floatval($summary->shipping ?? 0) : 0; // Set shipping to 0 when hidden
$reward_price = floatval($summary->reward_price ?? 0);
$total = $bonus + $shipping + $reward_price;

// Use growfund_to_currency helper for consistent currency formatting
?>
<div class="growfund-order-summary">
    <?php if ($show_bonus) : ?>
        <div class="growfund-order-row">
            <span><?php echo esc_html__('Bonus Support', 'growfund'); ?></span>
            <div class="growfund-bonus-support-input-wrapper">
                <?php
                growfund_renderer()->render('site.components.input', [
                    'type' => 'number',
                    'name' => 'bonus_support',
                    'value' => (float) $summary->bonus_support,
                    'min' => '0',
                    'class' => 'growfund-input-bonus-support',
                    'variant' => 'simple',
                ]);
                ?>
            </div>
        </div>
    <?php endif; ?>
    <?php if ($show_shipping) : ?>
        <div class="growfund-order-row growfund-shipping-row">
            <span><?php echo esc_html__('Shipping', 'growfund'); ?></span>
            <span class="growfund-shipping-amount"><?php echo esc_html(growfund_to_currency($shipping)); ?></span>
        </div>
    <?php endif; ?>
    <div class="growfund-order-total growfund-order-row">
        <span><?php echo esc_html__('Total amount', 'growfund'); ?></span>
        <span class="growfund-order-total-amount" id="growfund-order-total-amount" data-shipping="<?php echo esc_attr($shipping); ?>" data-reward-price="<?php echo esc_attr($reward_price); ?>"><?php echo esc_html(growfund_to_currency($total)); ?></span>
    </div>
</div>