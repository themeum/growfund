<?php
/**
 * @var Growfund\Views\Components\UI\SocialShare $social_share
 */

defined( 'ABSPATH' ) || exit;

?>

<div class="growfund-social-share">
    <?php if ($social_share->label) : ?>
        <span><?php echo esc_html($social_share->label); ?></span>
    <?php endif; ?>
    <div class="growfund-social-share-button">
        <?php foreach ($social_share->get_share_platforms() as $growfund_social_platform) : ?>
            <a class="growfund-share-btn" 
                data-social-platform="<?php echo esc_attr($growfund_social_platform['key']); ?>" 
                href="<?php echo esc_url($growfund_social_platform['share_link']); ?>" 
                aria-label="<?php echo esc_attr($growfund_social_platform['aria_label']); ?>" 
            >
                <?php
                if (!empty($growfund_social_platform['icon'])) {
                    growfund_echo_safe_html($growfund_social_platform['icon']);
                }
                ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>