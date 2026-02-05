<?php
/**
 * @var Growfund\Views\Components\Campaign\CampaignSlider $campaign_slider
 */

defined( 'ABSPATH' ) || exit;

use Growfund\Views\Components\Campaign\CampaignList;

?>

<div 
    <?php echo $campaign_slider->id ? ' id="' . esc_attr($campaign_slider->id) . '"' : ''; ?>
    class="growfund-campaign-slider <?php echo $campaign_slider->classname ? esc_attr(' ' . $campaign_slider->classname) : ''; ?>"
>
    <div class="growfund-campaign-slider-inner">
        <div class="growfund-campaign-slider-header-wrapper">
            <h4 class="growfund-campaign-slider-header"><?php echo esc_html($campaign_slider->label ?? 'Campaigns'); ?></h4>
            <div class="growfund-campaign-slider-controls">
                <button class="growfund-campaign-slider-arrow-left" type="button">
                    <?php growfund_echo_safe_html($campaign_slider->get_svg_icon('assets/site/icon/chevron-left.svg')); ?>
                </button>

                <button class="growfund-campaign-slider-arrow-right" type="button">
                    <?php growfund_echo_safe_html($campaign_slider->get_svg_icon('assets/site/icon/chevron-right.svg')); ?>
                </button>
            </div>
        </div>
   
        <div class="growfund-campaign-slider-window">
            <?php 
                $growfund_campaign_list = new CampaignList();
                $growfund_campaign_list->campaigns = $campaign_slider->campaigns ?? [];
                $growfund_campaign_list->id = $campaign_slider->id ? $campaign_slider->id . '_campaign_list' : null;
                $growfund_campaign_list->classname = 'growfund-campaign-slider-track';

                growfund_render($growfund_campaign_list);
            ?>
        </div>
    </div>
</div>
    