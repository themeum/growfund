<?php
/**
 * @var Growfund\Views\Components\UI\Tab $tab
 */

defined('ABSPATH') || exit;

?>


<?php if (!empty($tab->tabs) && is_array($tab->tabs)) : ?>

<div 
    class="growfund-tab-wrapper" data-growfund-tab-contents
    <?php echo $tab->id ? 'id="' . esc_attr($tab->id) . '"' : ''; ?>
>
    <div class="growfund-tab-contents-header-wrapper">
            <div class="growfund-tab-contents-header">
        
            <?php foreach ($tab->tabs as $growfund_index => $growfund_tab_item) : ?>
                <button
                    type="button"
                    class="growfund-tab-contents-item"
                    data-growfund-tab-contents-trigger="<?php echo esc_attr($growfund_tab_item['key']); ?>"
                    aria-selected="<?php echo $growfund_index === 0 ? 'true' : 'false'; ?>"
                >
                    <?php echo esc_html($growfund_tab_item['label']); ?>
                   
                </button>
            <?php endforeach; ?>
                </div>
            <?php if ($tab->allow_header_button) : ?>
            <div class="growfund-tab-contents-header-back-button-wrapper">
                
				<?php growfund_render($tab->header_button); ?>
                
            </div>
        <?php endif; ?>
    </div>
    <div class="growfund-tab-contents-panels">
        <?php foreach ($tab->tabs as $growfund_index => $growfund_tab_item) : ?>
            <div
                class="growfund-tab-contents-panel"
                data-growfund-tab-contents-panel="<?php echo esc_attr($growfund_tab_item['key']); ?>"
                aria-hidden="<?php echo $growfund_index === 0 ? 'false' : 'true'; ?>"
            >
                <?php growfund_echo_safe_html($growfund_tab_item['content']); ?>
            </div>
        <?php endforeach; ?>
    </div>

</div>

<?php endif; ?>
