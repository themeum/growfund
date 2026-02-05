<?php 

/**
 * @var Growfund\Views\Components\UI\Badge $badge
 */

defined('ABSPATH') || exit;

function growfund_get_badge_variant($variant) {
    switch ($variant) {
        case 'success':
            return 'growfund-badge-success-text';
        case 'error':
            return 'growfund-badge-error-text';
        case 'info':
            return 'growfund-badge-info-text';
        default:
            return 'growfund-badge-warning-text';
    }
}

?>

<?php if (!empty($badge->message)) : ?>

<div class="growfund-badge-container <?php echo esc_attr(growfund_get_badge_variant($badge->variant)); ?>
    <?php echo esc_attr($badge->classname ?? ''); ?>">
	<?php if ($badge->svg_icon) : ?>
        <span class="growfund-badge-icon">
            <?php growfund_echo_safe_html($badge->get_badge_icon()); ?> 
        </span>
    <?php endif; ?>
    <span class="growfund-badge-text <?php echo esc_attr(growfund_get_badge_variant($badge->variant)); ?>">
        <?php echo esc_html($badge->message); ?>
    </span>
</div>

<?php endif; ?>