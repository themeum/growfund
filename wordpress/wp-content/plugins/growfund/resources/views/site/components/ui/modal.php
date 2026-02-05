<?php
/**
 * @var Growfund\Views\Components\UI\Modal $modal
 */

defined( 'ABSPATH' ) || exit;
?>

<div id="<?php echo esc_attr($modal->id); ?>" class="growfund-modal <?php echo esc_attr($modal->classname); ?>">
    <div class="growfund-modal-overlay"></div>
    
    <div class="growfund-modal-content">
        <?php if ($modal->show_header) : ?>
        <div class="growfund-modal-header">
            <div class="growfund-modal-header-left">
                <span class="growfund-modal-close-button-icon"><?php growfund_echo_safe_html($modal->get_header_icon()); ?> </span>
                <h2 class="growfund-modal-title"><?php echo esc_html($modal->title); ?></h2>
                
            </div>
            <span class="growfund-modal-close-button-icon">
				<?php growfund_echo_safe_html($modal->get_svg_icon('assets/site/icon/cross.svg')); ?>
            </span>
        </div>
        <?php endif; ?>

        <div class="growfund-modal-body">
            <?php growfund_echo_safe_html($modal->body_content); ?>
        </div>

        <?php if ($modal->show_footer) : ?>
            <div class="growfund-modal-footer">
                <button type="button" class="growfund-modal-cancel-button">
                    <?php echo esc_html($modal->cancel_label); ?>
                </button>
                <button type="button" class="growfund-modal-apply-button">
                    <?php echo esc_html($modal->apply_label); ?>
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>