<?php
/**
 * @var Growfund\Views\Components\CampaignUpdate\UpdateList $update_list
 */

defined('ABSPATH') || exit;


use Growfund\Views\Components\CampaignUpdate\UpdateCard;


if (!empty($update_list->updates)) {
    foreach ($update_list->updates as $growfund_campaign_update) {
        $growfund_campaign_update_card = new UpdateCard();
        $growfund_campaign_update_card->update = $growfund_campaign_update;
        $growfund_campaign_update_card->campaign_id = $update_list->campaign_id;
        
        growfund_render($growfund_campaign_update_card);
    }
}
