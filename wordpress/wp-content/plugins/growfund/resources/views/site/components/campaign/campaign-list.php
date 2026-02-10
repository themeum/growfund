<?php
/**
 * @var Growfund\Views\Components\Campaign\CampaignList $campaign_list
 */

use Growfund\Views\Components\Campaign\CampaignCard;

defined( 'ABSPATH' ) || exit;

?>

<div 
    <?php echo $campaign_list->id ? 'id="' . esc_attr($campaign_list->id) . '"' : ''; ?>
    <?php echo $campaign_list->classname ? 'class="' . esc_attr($campaign_list->classname) . '"' : ''; ?>
    data-has-more="<?php echo $campaign_list->has_more ? 'true' : 'false'; ?>"
>
    <?php
    foreach ($campaign_list->campaigns as $growfund_campaign) {
        $growfund_campaign_card = new CampaignCard ();
        $growfund_campaign_card->campaign = $growfund_campaign;
        growfund_render($growfund_campaign_card);
    } 
    ?>
</div>