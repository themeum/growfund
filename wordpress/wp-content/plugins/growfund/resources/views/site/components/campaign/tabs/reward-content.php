<?php

/** @var Growfund\Views\Components\Campaign\Tabs\RewardContent $reward_content */

use Growfund\Views\Components\Campaign\RewardCollection;

defined( 'ABSPATH' ) || exit;

?>


<div class="growfund-reward-tab-content-grid">
    <?php
        $growfund_reward_collection = new RewardCollection();
        $growfund_reward_collection->campaign = $reward_content->campaign;
        $growfund_reward_collection->rewards = $reward_content->rewards;

        growfund_render($growfund_reward_collection);
    ?>
</div>