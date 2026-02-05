<?php
/**
 * @var Growfund\Views\Components\Campaign\RewardCollection $reward_collection
 */

use Growfund\Views\Components\Campaign\CampaignRewardCard;

defined( 'ABSPATH' ) || exit;


foreach ($reward_collection->rewards as $growfund_reward) {
	$growfund_reward_card = new CampaignRewardCard();
	$growfund_reward_card->reward = $growfund_reward;
	$growfund_reward_card->campaign = $reward_collection->campaign;
	growfund_render($growfund_reward_card);
}
