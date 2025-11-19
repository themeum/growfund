<?php

defined( 'ABSPATH' ) || exit;

/**
 * Rewards Tab Content Component
 */

$rewards = $rewards ?? [];
$campaign = $campaign ?? null;
$campaignId = $campaign_id ?? null;

?>

<div class="growfund-tab-content growfund-tab-content--rewards" data-tab="rewards" <?php echo $campaignId ? 'data-campaign-id="' . esc_attr($campaignId) . '"' : ''; ?>>
    <div class="growfund-tab-content__container">
        <div class="growfund-tab-content__body">
            <?php if (!empty($rewards)) : ?>
                <div class="growfund-rewards-grid" id="growfund-rewards-grid">
                    <?php
                    growfund_renderer()
                        ->render('site.components.campaign-reward', [
                            'rewards' => $rewards,
                            'campaign_id' => $campaignId,
                            'is_closed' => $campaign->is_closed
                        ]);
					?>
                </div>
            <?php else : ?>
                <div class="growfund-no-rewards">
                    <p><?php esc_html_e('No rewards available for this campaign.', 'growfund'); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>