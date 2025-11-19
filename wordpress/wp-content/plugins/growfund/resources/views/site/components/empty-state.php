<?php

defined( 'ABSPATH' ) || exit;

/**
 * Empty State Component
 * For use when no reward is selected in checkout
 */
?>
<div class="growfund-empty-state">
    <?php
    growfund_renderer()->render('site.components.icon', [
		'name' => 'pledge-without-reward',
		'size' => 80,
		'attributes' => ['class' => 'growfund-empty-icon']
	]);
    ?>
    <div class="growfund-empty-content">
        <div class="growfund-empty-title">You are Pledging Without a Reward</div>
        <div class="growfund-empty-message">Thank you for your support!</div>
        <a href="<?php echo esc_url(home_url('/campaigns/' . $campaign_slug . '/#rewards')); ?>" class="growfund-empty-action">Choose a Reward</a>
    </div>
</div>