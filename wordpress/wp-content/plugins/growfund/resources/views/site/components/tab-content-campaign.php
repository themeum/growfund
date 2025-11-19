<?php

defined( 'ABSPATH' ) || exit;

/**
 * Campaign Tab Content Component
 */

$campaign = $campaign ?? null;

// Determine the tab name based on donation mode
$tabName = growfund_app()->is_donation_mode() ? 'info' : 'campaign';

?>

<div class="growfund-tab-content growfund-tab-content--campaign" data-tab="<?php echo esc_attr($tabName); ?>">
    <div class="growfund-tab-content__container">
        <div class="growfund-tab-content__body">
            <div class="growfund-campaign">
                <div class="growfund-campaign__container">
                    <div class="growfund-campaign__main">
                        <div class="growfund-content-section">
                            <h3 class="growfund-content-section__title"><?php echo esc_html__('Story', 'growfund'); ?></h3>

                            <?php if ($campaign && !empty($campaign->story)) : ?>
                                <div class="growfund-rich-text-content">
                                    <?php echo wp_kses_post($campaign->story); ?>
                                </div>
                            <?php else : ?>
                                <p><?php esc_html_e('No story content available for this campaign.', 'growfund'); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if (!growfund_app()->is_donation_mode()) : ?>
                        <div class="growfund-campaign__sidebar">
                            <?php
                            growfund_renderer()
                                ->render('site.components.support-section', [
                                    'campaign' => $campaign,
                                    'is_donation_mode' => growfund_app()->is_donation_mode()
                                ]);
							?>

                            <?php
                            // Use pre-prepared rewards data from controller
                            $rewards = $campaign->rewards ?? [];

                            // Only show the first three rewards
                            $displayRewards = array_slice($rewards, 0, 3);

                            // Render rewards if available
                            if (!empty($displayRewards)) {
                                growfund_renderer()
                                    ->render('site.components.campaign-reward', [
										'rewards' => $displayRewards,
										'is_closed' => $campaign->is_closed ?? ''
									]);
                            }
                            ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>