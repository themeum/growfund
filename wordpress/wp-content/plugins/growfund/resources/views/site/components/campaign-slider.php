<?php

defined( 'ABSPATH' ) || exit;

/**
 * Campaign Slider Template
 *
 * @var array $campaigns
 * @var string $variant
 * @var int $total
 * @var int $per_page
 * @var int $current_page
 * @var bool $has_more
 * @var array $filter_state
 * @var string $header
 * @var int $limit
 * @var int $total_loaded
 * @var array $all_campaigns
 */
?>

<div class="growfund-campaign-slider <?php echo $variant === 'featured' ? 'growfund-campaign-slider--featured' : ''; ?>"
    data-total="<?php echo esc_attr($total); ?>"
    data-per-page="<?php echo esc_attr($per_page); ?>"
    data-current-page="<?php echo esc_attr($current_page); ?>"
    data-has-more="<?php echo esc_attr($has_more ? '1' : '0'); ?>"
    data-variant="<?php echo esc_attr($variant); ?>"
    data-total-loaded="<?php echo esc_attr($total_loaded ?? count($campaigns)); ?>"
    data-limit="<?php echo esc_attr($limit ?? 12); ?>">
    <div class="growfund-campaign-slider__header">
        <h2 class="growfund-section-header"><?php echo esc_html($header); ?></h2>
        <?php
        // Show navigation if there are more items than can be displayed at once OR if there are more items available to load
        $all_campaigns = $all_campaigns ?? $campaigns;
        $total_items = count($all_campaigns);
        $show_navigation = $total_items > $per_page || $has_more;
        ?>
        <?php if ($show_navigation) : ?>
            <div class="growfund-campaign-slider__navigation">
                <button type="button" class="growfund-campaign-slider__button growfund-campaign-slider__button--prev">
                    <?php growfund_renderer()->render('site.components.icon', ['name' => 'chevron-left']); ?>
                </button>
                <button type="button" class="growfund-campaign-slider__button growfund-campaign-slider__button--next">
                    <?php growfund_renderer()->render('site.components.icon', ['name' => 'chevron-right']); ?>
                </button>
            </div>
        <?php endif; ?>
    </div>
    <div class="growfund-campaign-slider__track">
        <?php
        // Render all campaigns - let JavaScript handle the sliding
        $all_campaigns = $all_campaigns ?? $campaigns;
        foreach ($all_campaigns as $index => $campaign) :
			?>
            <?php
            growfund_renderer()
                ->render('site.components.campaign-slider-item', [
                    'campaign' => $campaign,
                    'variant' => $variant === 'featured' ? 'featured' : 'list',
                    'is_hidden' => '' // Don't hide items initially
                ]);
            ?>
        <?php endforeach; ?>
    </div>
</div>