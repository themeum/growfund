<?php

defined( 'ABSPATH' ) || exit;

/**
 * Updates Tab Content Component
 */

$updates = $updates ?? [];
$campaign = $campaign ?? null;
$campaignId = $campaign_id ?? null;

// Get timeline dates from controller - already formatted
$timelineDates = $campaign->timeline_dates ?? null;
$startDateFormatted = $timelineDates->start_date_formatted ?? wp_date('M j');
$endDateFormatted = $timelineDates->end_date_formatted ?? wp_date('M j');
$launchDateFormatted = $timelineDates->launch_date_formatted ?? wp_date('F j, Y');

// Get total updates count from pre-prepared data
$totalUpdatesCount = 0;
if (is_object($campaign) && isset($campaign->total_campaign_updates_count)) {
    $totalUpdatesCount = $campaign->total_campaign_updates_count;
}
?>

<div class="growfund-tab-content growfund-tab-content--updates" data-tab="updates" <?php echo $campaignId ? 'data-campaign-id="' . esc_attr($campaignId) . '"' : ''; ?> <?php echo $totalUpdatesCount > 0 ? 'data-total-updates-count="' . esc_attr($totalUpdatesCount) . '"' : ''; ?>>
    <div class="growfund-tab-content__container">

        <!-- Updates List View -->
        <div class="growfund-tab-content__body growfund-updates-list-view" id="updates-list-view">
            <?php if (!empty($updates)) : ?>
                <div class="growfund-updates-wrapper">
                    <div class="growfund-updates-wrapper__top">
                        <div class="growfund-updates-timeline">
                            <div class="growfund-timeline-start">
                                <span class="growfund-timeline-date-label"><?php echo esc_html($startDateFormatted); ?></span>
                            </div>

                            <div class="growfund-timeline-line-container">
                                <div class="growfund-timeline-line-background"></div>
                                <div class="growfund-timeline-line-progress"></div>
                                <div class="growfund-timeline-dot-current"></div>
                            </div>

                            <div class="growfund-timeline-end">
                                <span class="growfund-timeline-date-label"><?php echo esc_html($endDateFormatted); ?></span>
                            </div>

                            <div class="growfund-timeline-progress-text">
                                <span class="growfund-timeline-count">1/<?php echo esc_html($totalUpdatesCount > 0 ? $totalUpdatesCount : 0); ?></span>
                                <span class="growfund-timeline-current-date"><?php echo esc_html($startDateFormatted); ?></span>
                            </div>
                        </div>

                        <div class="growfund-updates-list" id="growfund-updates-list">
                            <?php foreach ($updates as $update) : ?>
                                <?php
                                growfund_renderer()
                                    ->render('site.components.update-item', ['data' => $update]);
								?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="growfund-updates__project-launch">
                        <div class="growfund-updates__project-launch-date">
                            <span class="growfund-updates__project-launch-date-text"><?php esc_html_e('Campaign launches', 'growfund'); ?></span>
                            <span class="growfund-updates__project-launch-date-time"><?php echo esc_html($launchDateFormatted); ?></span>
                        </div>
                    </div>
                </div>
            <?php else : ?>
                <div class="growfund-no-updates">
                    <p><?php esc_html_e('No updates available yet.', 'growfund'); ?></p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Update Detail Views -->
        <?php if (!empty($updates)) : ?>
            <?php foreach ($updates as $singleUpdateDTO) : ?>
                <?php
                growfund_renderer()
                    ->render('site.components.update-detail-view', [
                        'data' => $singleUpdateDTO,
                        'social_sharing_options' => growfund_social_sharing_options()
                    ]);
				?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>