<?php
/**
 * @var Growfund\Views\Components\Campaign\CampaignBookmarkButton $campaign_bookmark_button
 */

defined( 'ABSPATH' ) || exit;


$growfund_bookmarked_label = __('Bookmarked', 'growfund');
$growfund_bookmark_label = __('Save for later', 'growfund');

?>

<?php if (!$campaign_bookmark_button->campaign->is_ended) : ?>
    <button 
        type="button"
        <?php echo $campaign_bookmark_button->id ? ' id="' . esc_attr($campaign_bookmark_button->id) . '"' : ''; ?>
        class="
        growfund-campaign-bookmark-button 
        <?php echo esc_attr($campaign_bookmark_button->classname ? $campaign_bookmark_button->classname : ''); ?> 
        <?php echo esc_attr($campaign_bookmark_button->campaign->is_bookmarked ? 'growfund-campaign-is-bookmarked' : ''); ?>
        "
        aria-label="Bookmark campaign"
        data-campaign-id="<?php echo esc_attr($campaign_bookmark_button->campaign->id); ?>"
        data-campaign-is-bookmarked="<?php echo esc_attr($campaign_bookmark_button->campaign->is_bookmarked ? 'true' : 'false'); ?>"
        data-redirect-url="<?php echo growfund_user()->is_logged_in() ? '' : esc_url(growfund_login_url(growfund_campaign_url($campaign_bookmark_button->campaign->slug))); ?>"
    >
        <svg class="growfund-campaign-bookmark-icon" width="16" height="16" viewBox="0 0 24 24" stroke="#338C58"fill="none" aria-hidden="true">
            <path d="M6 2a2 2 0 0 0-2 2v18l8-5 8 5V4a2 2 0 0 0-2-2z"/>
        </svg>
        <?php if ($campaign_bookmark_button->has_label) : ?>
            <span class="growfund-campaign-bookmark-button-label" data-bookmarked-label="<?php echo esc_attr($growfund_bookmarked_label); ?>" data-bookmark-label="<?php echo esc_attr($growfund_bookmark_label); ?>">
                <?php echo esc_html($campaign_bookmark_button->campaign->is_bookmarked ? $growfund_bookmarked_label : $growfund_bookmark_label); ?>
            </span>
        <?php endif; ?>
    </button>
<?php endif; ?>