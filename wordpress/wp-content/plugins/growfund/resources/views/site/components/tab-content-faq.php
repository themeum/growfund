<?php

defined( 'ABSPATH' ) || exit;

/**
 * FAQ Tab Content Component
 */

$faqs = $faqs ?? [];
?>

<div class="growfund-tab-content growfund-tab-content--faq" data-tab="faq">
    <div class="growfund-tab-content__container">
        <div class="growfund-faq-layout">
            <div class="growfund-faq-header">
                <h2 class="growfund-faq-title"><?php esc_html_e('Frequently', 'growfund'); ?><br><?php esc_html_e('asked questions', 'growfund'); ?></h2>
            </div>

            <?php
            growfund_renderer()
                ->render('site.components.faq', [
                    'faqs' => $faqs
                ]);
			?>
        </div>
    </div>
</div>