<?php

defined( 'ABSPATH' ) || exit;

/**
 * Pledge Modal Component
 * 
 * Modal that opens when "Back this campaign" button is clicked
 * Contains pledge amount input and available rewards for selection
 * 
 * @param array $data Component data containing:
 *   - campaign: Campaign object with id, title, description, image
 *   - rewards: Array of available rewards for the campaign
 *   - min_pledge_amount: Minimum pledge amount allowed
 *   - max_pledge_amount: Maximum pledge amount allowed (optional)
 */

use Growfund\Constants\AppreciationType;
use Growfund\Core\CurrencyConfig;
use Growfund\Supports\Utils;

$defaults = [
    'campaign' => null,
    'rewards' => [],
    'min_pledge_amount' => 1,  // Changed from 25 to 1
    'max_pledge_amount' => null
];

$data = array_merge($defaults, (array) $data ?? []);

$campaign = $data['campaign'];
$rewards = $data['rewards'] ?? [];

// Get the actual minimum pledge amount from campaign data if available
$min_pledge_amount = $data['min_pledge_amount'] ?? 1;  // Default to 1 instead of 25

// If campaign data contains min_pledge_amount, use that instead
if ($campaign && is_object($campaign) && isset($campaign->min_pledge_amount)) {
    $min_pledge_amount = max(1, (float) $campaign->min_pledge_amount);  // Ensure at least $1
}

$max_pledge_amount = $campaign->allow_pledge_without_reward ? $data['max_pledge_amount'] : null;

// Build checkout URLs
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

?>

<div id="growfund-pledge-modal" class="growfund-pledge-modal">
    <div class="growfund-pledge-modal__overlay"></div>
    <div class="growfund-pledge-modal__content">
        <div class="growfund-pledge-modal__header">
            <div class="growfund-pledge-modal__header-left">
                <?php
                if (!empty($rewards)) {
                    growfund_renderer()->render('site.components.icon', [
						'name' => 'gift',
						'size' => 'md',
						'attributes' => ['class' => 'growfund-pledge-modal__icon']
					]);
                }
                ?>
                <h2 class="growfund-pledge-modal__title"><?php echo !empty($rewards) ? esc_html__('Select your reward', 'growfund') : esc_html__('Back this campaign', 'growfund'); ?></h2>
            </div>
            <?php
            growfund_renderer()->render('site.components.icon', [
				'name' => 'cross',
				'size' => 'md',
				'attributes' => [
					'id' => 'growfund-pledge-modal-close',
					'class' => 'growfund-pledge-modal__close'
				]
			]);
			?>
        </div>

        <div class="growfund-pledge-modal__body">
            <?php if ($campaign->allow_pledge_without_reward || $campaign->appreciation_type === AppreciationType::GIVING_THANKS) : ?>
                <!-- Pledge without reward section -->
                <div class="growfund-pledge-modal__section growfund-pledge-without-reward">
                    <?php if (!empty($rewards)) : ?>
                        <h3 class="growfund-pledge-section__title"><?php esc_html_e('Pledge without a reward', 'growfund'); ?></h3>
                    <?php endif; ?>

                    <div class="growfund-pledge-form">
                        <label for="growfund-pledge-amount-input" class="growfund-pledge-form__label"><?php !empty($rewards) ? esc_html_e('Amount', 'growfund') : esc_html_e('Pledge Amount', 'growfund'); ?></label>
                        <div class="growfund-pledge-input-group">
                            <?php
                            try {
                                $currency_config = growfund_app()->make(CurrencyConfig::class)->get();
                                $currency_parts = explode(':', $currency_config['currency']);
                                $currency_symbol = $currency_parts[0] ?? '$';
                            } catch (Exception $error) {
                                $currency_symbol = '$';
                            }
                            ?>
                            <?php
                            growfund_renderer()
                                ->render('site.components.input', [
                                    'type' => 'number',
                                    'variant' => 'currency',
                                    'id' => 'growfund-pledge-amount-input',
                                    'name' => 'pledge_amount',
                                    'value' => number_format((float) $min_pledge_amount, 2, '.', ''),
                                    'min' => $min_pledge_amount,
                                    'attributes' => array_filter([
                                        'max' => $max_pledge_amount,
                                        'data-min-amount' => $min_pledge_amount,
                                        'data-max-amount' => $max_pledge_amount,
                                        'data-currency-symbol' => $currency_symbol
                                    ])
                                ]);
							?>

                            <?php
                            growfund_renderer()
                                ->render('site.components.button', [
                                    /* translators: %s: pledge amount */
                                    'text' => sprintf(esc_html__('Pledge %s', 'growfund'), growfund_to_currency($min_pledge_amount)),
                                    'class' => 'growfund-btn--primary growfund-pledge-btn growfund-branding-btn',
                                    'id' => 'growfund-pledge-no-reward-btn',
                                    'attributes' => [
                                        'data-campaign-id' => $campaign_id,
                                        'data-checkout-url' => $checkout_url_no_reward
                                    ]
                                ]);
							?>
                        </div>
                        <?php if ($campaign->allow_pledge_without_reward) : ?>
                            <div class="growfund-pledge-input-hint">Enter an amount between <?php echo esc_html(growfund_to_currency($min_pledge_amount)); ?> and <?php echo esc_html(growfund_to_currency($max_pledge_amount)); ?> </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif ?>

            <!-- Available rewards section -->
            <?php if (!empty($rewards)) : ?>
                <div class="growfund-pledge-modal__section growfund-available-rewards">
                    <h3 class="growfund-pledge-section__title"><?php esc_html_e('Available rewards', 'growfund'); ?></h3>

                    <div class="growfund-pledge-rewards-list">
                        <?php foreach ($rewards as $reward) : ?>
                            <?php

                            $reward->variant = 'pledge';

                            // Render the reward component directly
                            growfund_renderer()
                                ->render('site.components.campaign-reward', [
                                    'rewards' => [$reward],
                                    'campaign' => $campaign
                                ]);
                            ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>