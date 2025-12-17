<?php

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\HookNames;

$initial_has_more = $data['campaigns']->has_more ?? false;
$initial_total_campaigns = $data['campaigns']->total ?? 0;

growfund_localize_script(HookNames::WP_ENQUEUE_SCRIPT, 'growfundArchiveData', [
	'initialHasMore' => wp_json_encode($initial_has_more),
	'initialPage' => 2, // Next page to load,
	'initialLimit' => wp_json_encode($initial_limit),
	'initialTotalCampaigns' => wp_json_encode($initial_total_campaigns),
]);
?>

<!-- Hero Section -->
<section class="growfund-hero">
    <div class="growfund-hero__background"></div>
    <div class="growfund-container">
        <div class="growfund-hero__content">
            <?php if (!empty($banner_title)) : ?>
                <h1 class="growfund-hero__title"><?php echo wp_kses_post($banner_title); ?></h1>
            <?php else : ?>
                <h1 class="growfund-hero__title"><?php esc_html_e('Support causes', 'growfund'); ?><br><?php esc_html_e('that matter', 'growfund'); ?></h1>
            <?php endif; ?>
        </div>
        <div class="growfund-hero__image">
            <!-- Hero image placeholder -->
        </div>
    </div>
</section>

<!-- Main Content -->
<div class="growfund-container growfund-main-content">
    <div class="growfund-content-layout">
        <!-- Filters -->
        <?php
        growfund_renderer()
            ->render('site.components.filters', [
                'categories' => $data['categories'] ?? [],
                'filter_state' => $filter_state,
            ]);
        ?>

        <!-- Main Content Area -->
        <main class="growfund-main-area">
            <?php
            $is_search = !empty(trim($filter_state['search'] ?? '')) ||
                !empty($filter_state['category'] ?? '') ||
                !empty($filter_state['location'] ?? '') ||
                !empty($filter_state['sort'] ?? '');
            ?>
            <!-- Always include the campaign list container for infinite scroll -->
            <div id="growfund-campaign-list-container">
                <?php if (!empty($data['campaigns']) && count($data['campaigns']->results)) : ?>
                    <?php if (!$is_search && !empty($featured_data['campaigns']->total)) : ?>
                        <!-- Featured projects slider (only show on default view) -->
                        <div class="growfund-featured-projects">
                            <?php
                            // Use the featured campaigns data directly
                            $featured_campaigns = $featured_data['campaigns']->results;

                            // Show initial featured campaigns in the slider
                            $slider_campaigns = $featured_campaigns;
                            $campaign_per_page = 2; // Show 2 items per view

                            growfund_renderer()
                                ->render('site.components.campaign-slider', [
                                    'campaigns' => $slider_campaigns, // Initial featured campaigns
                                    'all_campaigns' => $slider_campaigns, // Initial featured campaigns
                                    'variant' => 'featured',
                                    'total' => $featured_data['campaigns']->total ?? 0, // Total available featured campaigns
                                    'per_page' => $campaign_per_page,
                                    'current_page' => 1,
                                    'has_more' => $featured_data['campaigns']->has_more ?? false, // Enable dynamic loading if more available
                                    'header' => esc_html__('Featured Campaigns', 'growfund'),
                                    'limit' => $featured_initial_limit, // Load more items at a time for testing
                                    'total_loaded' => count($slider_campaigns)
                                ]);
                            ?>
                        </div>
                    <?php endif; ?>

                    <?php
                    growfund_renderer()
                        ->render('site.components.campaign-list', [
                            'campaigns' => $data['campaigns'],
                            'header' => $is_search ? '' : esc_html__('All Campaigns', 'growfund'),
                            'is_search' => $is_search
                        ]);
                    ?>
                <?php else : ?>
                    <div class="growfund-no-campaigns">
                        <div class="growfund-no-campaigns__content">
                            <h2><?php esc_html_e('No campaigns found.', 'growfund'); ?></h2>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>