<?php

defined( 'ABSPATH' ) || exit;

/**
 * Campaign Slider Item Template
 * 
 * This template acts as a wrapper for the campaign card when it's inside a slider.
 *
 * @var array $campaign The campaign data object/array.
 * @var string $variant The visual variant of the card.
 * @var string $is_hidden Optional CSS class for hidden items.
 */
?>
<?php
// Convert array to object if needed
$campaign_obj = is_array($campaign) ? (object) $campaign : $campaign;
?>
<div class="growfund-campaign-slider__item" data-campaign-id="<?php echo esc_attr($campaign_obj->id); ?>">
    <?php
    growfund_renderer()
        ->render('site.components.campaign-card', [
            'campaign' => $campaign_obj,
            'variant' => $variant,
        ]);
    ?>
</div>