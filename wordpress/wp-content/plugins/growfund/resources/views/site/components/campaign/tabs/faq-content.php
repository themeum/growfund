<?php

/** @var Growfund\Views\Components\Campaign\Tabs\FaqContent $faq_content */

use Growfund\Views\Components\UI\FaqList;

defined( 'ABSPATH' ) || exit;

?>

<div class="growfund-faq-tab-content-container">
    <div class="growfund-faq-tab-content-header">
        <h2 class="growfund-faq-tab-content-title"><?php esc_html_e('Frequently asked questions', 'growfund'); ?></h2>
    </div>       
    <?php 
        $growfund_faq_list = new FaqList();
        $growfund_faq_list->faqs = $faq_content->faqs;
        $growfund_faq_list->default_open_index = 0;
        growfund_render($growfund_faq_list);
    ?>
</div>

