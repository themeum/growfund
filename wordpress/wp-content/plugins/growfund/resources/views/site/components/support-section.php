<?php

defined( 'ABSPATH' ) || exit;

/**
 * Support Section Component
 * 
 * Displays the support section with pledge form and continue button
 * 
 * @param array|object $campaign Campaign object or array containing campaign data
 * @param bool $is_donation_mode Whether the site is in donation mode
 */

use Growfund\Supports\Utils;

$campaign = $campaign ?? new stdClass();
$is_donation_mode = $is_donation_mode ?? false;

// Build checkout URL for pledge without reward (same logic as pledge modal)
$checkout_url_no_reward = '#';
$campaign_id = $campaign->id ?? null;

if ($campaign_id) {
    $is_user_logged_in = growfund_user()->is_logged_in();

    if ($is_user_logged_in) {
        $checkout_url_no_reward = esc_url(Utils::get_checkout_url($campaign_id));
    } else {
        $checkout_url_no_reward = esc_url(growfund_login_url(growfund_campaign_url($campaign_id)));
    }
}

if ($campaign->allow_pledge_without_reward) :
	?>

    <div class="growfund-card growfund-support-section">
        <h3 class="growfund-support-section__title"><?php esc_html_e('Support', 'growfund'); ?></h3>
        <p class="growfund-support-subtitle"><?php esc_html_e('Make a pledge without a reward', 'growfund'); ?></p>

        <div class="growfund-pledge-form">
            <label for="pledge-amount" class="growfund-pledge-form__label"><?php esc_html_e('Pledge amount', 'growfund'); ?></label>
            <?php
            growfund_renderer()
                ->render('site.components.input', [
                    'type' => 'number',
                    'variant' => 'currency',
                    'id' => 'pledge-amount',
                    'value' => '0',
                ]);
			?>
        </div>

        <div class="growfund-support-message">
            <h4 class="growfund-support-message__title"><?php esc_html_e('Back it because you believe in it.', 'growfund'); ?></h4>
            <p class="growfund-support-message__description"><?php esc_html_e('Support the campaign for no reward, just because it speaks to you.', 'growfund'); ?></p>
        </div>

        <div class="growfund-pledge-continue">
            <?php
            growfund_renderer()
                ->render('site.components.button', [
                    'type' => 'button',
                    'class' => 'growfund-btn--primary',
                    'id' => 'growfund-pledge-continue',
                    'text' => esc_html__('Continue', 'growfund'),
                    'attributes' => [
                        'data-action' => 'direct-checkout',
                        'data-campaign-id' => $campaign_id ?? '',
                        'data-checkout-url' => $checkout_url_no_reward
                    ]
                ]);
			?>
        </div>
    </div>

<?php endif; ?>