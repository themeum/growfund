<?php
/**
 * @var Growfund\Views\Components\UI\InfiniteScroll $infinite_scroll
 */

defined('ABSPATH') || exit;

?>

<div id="<?php echo esc_attr($infinite_scroll->id); ?>" class="growfund-infinite-scroll">
    <div class="growfund-infinite-scroll-loader growfund-hidden">
        <?php echo esc_html__('Loading . . .', 'growfund'); ?>
    </div>
</div>
