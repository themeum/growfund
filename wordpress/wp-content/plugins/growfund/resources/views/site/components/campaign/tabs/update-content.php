<?php

/** @var Growfund\Views\Components\Campaign\Tabs\UpdateContent $update_content */

use Growfund\Views\Components\CampaignUpdate\UpdateContainer;

defined( 'ABSPATH' ) || exit;

?>
    <div class="growfund-update-tab-content-main-wrapper">
    <?php
        $growfund_campaign_update_container = new UpdateContainer();
        $growfund_campaign_update_container->campaign_id = $update_content->campaign_id;
        $growfund_campaign_update_container->updates     = $update_content->updates; 
        growfund_render($growfund_campaign_update_container);
	?>
    <div id="growfund_update_tab_content_detail" class="growfund-update-tab-content-detail">
    </div>
</div>
  
    
	

    

   
    
	
    
	
      

    