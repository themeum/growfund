<?php
/**
 * @var Growfund\Views\Components\Form\Button $button
 */

defined( 'ABSPATH' ) || exit;

?>

<?php if ($button->has_link) : ?>
    <a
        href="<?php echo $button->disabled ? '#' : esc_url($button->href); ?>"
        class="growfund-button <?php echo $button->classname ? esc_attr($button->classname) : ''; ?>"
        type="<?php echo esc_attr($button->type); ?>"
        <?php echo $button->id ? ' id="' . esc_attr($button->id) . '"' : ''; ?>
        <?php echo $button->style ? ' style="' . esc_attr($button->style) . '"' : ''; ?>
        <?php echo $button->disabled ? esc_attr(' disabled') : ''; ?>
    >
        <?php if ($button->icon_position === 'left' && $button->svg_icon) : ?>
            <span class="growfund-button-icon">
                <?php growfund_echo_safe_html($button->get_button_icon()); ?> 
            </span>
        <?php endif; ?>
        <?php echo esc_html($button->label); ?>
        <?php if ($button->icon_position === 'right' && $button->svg_icon) : ?>
            <span class="growfund-button-icon">
                <?php growfund_echo_safe_html($button->get_button_icon()); ?> 
            </span>
        <?php endif; ?>
    </a>
<?php else : ?>
    <button
        class="growfund-button <?php echo $button->classname ? esc_attr($button->classname) : ''; ?>"
        type="<?php echo esc_attr($button->type); ?>"
        <?php echo $button->id ? ' id="' . esc_attr($button->id) . '"' : ''; ?>
        <?php echo $button->style ? ' style="' . esc_attr($button->style) . '"' : ''; ?>
        <?php echo $button->disabled ? esc_attr(' disabled') : ''; ?>
    >
        <?php if ($button->icon_position === 'left' && $button->svg_icon) : ?>
            <span class="growfund-button-icon">
                <?php growfund_echo_safe_html($button->get_button_icon()); ?> 
            </span>
        <?php endif; ?>
        <?php echo esc_html($button->label); ?>
        <?php if ($button->icon_position === 'right' && $button->svg_icon) : ?>
            <span class="growfund-button-icon">
                <?php growfund_echo_safe_html($button->get_button_icon()); ?> 
            </span>
        <?php endif; ?>
    </button>
<?php endif; ?>


