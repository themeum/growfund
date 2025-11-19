<?php

defined( 'ABSPATH' ) || exit;

/**
 * Donation Item Component
 * Displays a single donation item with donor name, amount, and time
 * 
 * @param string $donor_name - Name of the donor
 * @param float $amount - Donation amount
 * @param int $timestamp - Timestamp of the donation
 * @param string $currency - Currency code (default: USD)
 * @param string $variant - Component variant (default: 'default', options: 'default', 'modal')
 */

use Growfund\Supports\Date;
use Growfund\Supports\Utils;

// Set default values for variables
$donor_name = $donor_name ?? '';
$amount = $amount ?? 0;
$timestamp = $timestamp ?? time();
$currency = $currency ?? Utils::get_currency();
$variant = $variant ?? 'default';
?>

<div class="growfund-donation-item<?php echo $variant !== 'default' ? ' growfund-donation-item--' . esc_attr($variant) : ''; ?>">
    <div class="growfund-donation-item__icon">
        <?php
        growfund_renderer()
            ->render('site.components.icon', [
                'name' => 'heart-handshake',
                'size' => 'lg'
            ]);
		?>
    </div>
    <div class="growfund-donation-item__content">
        <div class="growfund-donation-item__donor"><?php echo esc_html($donor_name); ?></div>
        <div class="growfund-donation-item__details">
            <span class="growfund-donation-item__amount"><?php echo esc_html(growfund_to_currency($amount)); ?></span>
            <span class="growfund-donation-item__separator">•</span>
            <span class="growfund-donation-item__time"><?php echo esc_html(Date::human_readable_time_diff(gmdate('Y-m-d H:i:s', $timestamp))); ?></span>
        </div>
    </div>
</div>