<?php

defined( 'ABSPATH' ) || exit;

/**
 * Recommendations Section Component
 * 
 * @param array $campaigns - Array of campaign data (primary parameter name)
 * @param array $recommendedProjects - Array of project data (alternative parameter name for backward compatibility)
 * @param string $title - Section title (default: "We also recommend")
 * @param string $exploreText - Explore link text (default: "Explore more")
 */

// Support both parameter names for flexibility
$campaigns = $campaigns ?? $recommendedProjects ?? [];
$title = $title ?? __('We also recommend', 'growfund'); // phpcs:ignore -- intentionally used
$exploreText = $exploreText ?? __('Explore more', 'growfund');

if (!is_iterable($campaigns)) {
    $campaigns = [];
}

?>

<?php if (!empty($campaigns)) : ?>
    <section class="growfund-recommendations">
        <div class="growfund-recommendations__header">
            <h2 class="growfund-recommendations__title"><?php echo esc_html($title); ?></h2>
            <a href="<?php echo esc_url(growfund_campaign_archive_url()); ?>" class="growfund-recommendations__explore">
                <?php echo esc_html($exploreText); ?>
                <span class="growfund-explore-icon"></span>
            </a>
        </div>
        <div class="growfund-recommendations__grid">
            <?php foreach ($campaigns as $campaign) : ?>
                <?php
                growfund_renderer()
                    ->render('site.components.campaign-card', [
                        'campaign' => $campaign,
                        'variant' => 'recommendation'
                    ]);
				?>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>