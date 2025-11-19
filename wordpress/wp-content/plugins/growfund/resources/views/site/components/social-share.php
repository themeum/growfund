<?php

defined( 'ABSPATH' ) || exit;

/**
 * Social Share Component
 *
 * @param string $social_shares - Array of social platforms to display
 * @param string $share_url - URL to share
 * @param string $share_text - Text to share
 * @since 1.0.0
 */

use Growfund\Constants\Site\SocialSharing;
?>

<div class="growfund-update-share">
    <?php


    if (!empty($social_shares)) :
		?>
        <span><?php esc_html_e('Share:', 'growfund'); ?></span>
        <div class="growfund-share-buttons">
            <?php
            foreach ($social_shares as $platform) {
                $icon_name  = $platform;
                $aria_label = '';
                $share_link = '#';

                // Map platform names to icon names and aria labels
                switch ($platform) {
                    case 'facebook':
                        $aria_label = esc_html__('Share on Facebook', 'growfund');
                        $share_link = str_replace(
                            ['{url}', '{text}'],
                            [urlencode($share_url), urlencode($share_text)],
                            SocialSharing::FACEBOOK_URL
                        );
                        break;
                    case 'x':
                        $aria_label = esc_html__('Share on X', 'growfund');
                        $share_link = str_replace(
                            ['{url}', '{text}'],
                            [urlencode($share_url), urlencode($share_text)],
                            SocialSharing::X_URL
                        );
                        break;
                    case 'linkedin':
                        $aria_label = esc_html__('Share on LinkedIn', 'growfund');
                        $share_link = str_replace(
                            ['{url}', '{title}', '{text}'],
                            [urlencode($share_url), urlencode($share_title), urlencode($share_text)],
                            SocialSharing::LINKEDIN_URL
                        );
                        break;
                    case 'whatsapp':
                        $aria_label = esc_html__('Share on WhatsApp', 'growfund');
                        $share_link = str_replace(
                            ['{text}', '{url}'],
                            [urlencode($share_text), urlencode($share_url)],
                            SocialSharing::WHATSAPP_URL
                        );
                        break;
                    case 'telegram':
                        $aria_label = esc_html__('Share on Telegram', 'growfund');
                        $share_link = str_replace(
                            ['{url}', '{text}'],
                            [urlencode($share_url), urlencode($share_text)],
                            SocialSharing::TELEGRAM_URL
                        );
                        break;

                    default:
                        /* translators: %s: platform name */
                        $aria_label = sprintf(esc_html__('Share on %s', 'growfund'), ucfirst($platform));
                        $share_link = '#';
                        break;
                }
				?>
                <a class="growfund-share-btn" data-platform="<?php echo esc_attr($platform); ?>" href="<?php echo esc_url($share_link); ?>" aria-label="<?php echo esc_attr($aria_label); ?>" <?php echo $platform === 'instagram' ? 'data-copy-url="' . esc_attr($share_url) . '"' : ''; ?>>
                    <?php
                    growfund_renderer()
                        ->render('site.components.icon', array(
                            'name' => $icon_name,
                            'size' => 'md',
                        ));
                    ?>
                </a>
				<?php
            }
            ?>
        </div>
    <?php endif; ?>
</div>