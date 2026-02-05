<?php
/**
 * @var Growfund\Views\Components\CampaignUpdate\UpdateDetailContainer $update_detail_container
 */

use Growfund\Views\Components\CampaignUpdate\UpdateDetail;

defined('ABSPATH') || exit;

?>
<div class="growfund-update-detail-container">
    <?php if (!empty($update_detail_container->updates)) : ?>
        <?php foreach ($update_detail_container->updates as $growfund_campaign_update) : ?>
            <div class="growfund-update-detail-item" data-update-id="<?php echo esc_attr($growfund_campaign_update->id); ?>">
                <?php
                $growfund_detail_view = new UpdateDetail();
                $growfund_detail_view->update = $growfund_campaign_update;
                growfund_render($growfund_detail_view);
                ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>