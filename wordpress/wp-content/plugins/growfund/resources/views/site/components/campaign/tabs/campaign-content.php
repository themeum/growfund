<?php

/** @var Growfund\Views\Components\Campaign\Tabs\CampaignContent $campaign_content */

use Growfund\Views\Components\Campaign\RewardCollection;

defined( 'ABSPATH' ) || exit;

?>

<div class="growfund-campaign-tab-content-campaign-container">
    <div class="growfund-campaign-tab-content-campaign-container-layout">
        <h3 class="growfund-campaign-tab-content-title"><?php esc_html_e('Story', 'growfund'); ?></h3>
        <div class="growfund-campaign-tab-content-rich-text growfund-rich-text-content">
            <?php growfund_echo_safe_html($campaign_content->campaign->story ?? ''); ?>
        </div>
    </div>

    <?php if (!empty($campaign_content->rewards)) : ?>
    <div class="growfund-campaign-tab-content-rewards-sidebar">
        <?php
            $growfund_reward_collection = new RewardCollection();
            $growfund_reward_collection->campaign = $campaign_content->campaign;
            $growfund_reward_collection->rewards = $campaign_content->rewards;

            growfund_render($growfund_reward_collection);
        ?>
    </div>
    <?php endif; ?>
</div>


