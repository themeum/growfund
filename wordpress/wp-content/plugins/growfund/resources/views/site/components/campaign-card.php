<?php

defined( 'ABSPATH' ) || exit;

/**
 * Campaign Card Component
 * 
 * @param array $campaign - Campaign data
 * @param string $variant - Card variant ('recommendation', 'list', or 'featured')
 */

use Growfund\Supports\GoalDisplay;

if (!function_exists('get_campaign_time_left')) {
    function get_campaign_time_left($end_date)
    {
        if (empty($end_date)) {
            return __('Active', 'growfund');
        }

        try {
            $end_date_obj = new DateTime($end_date);
            $now = new DateTime();

            if ($end_date_obj < $now) {
                return __('Ended', 'growfund');
            }

            $interval = $now->diff($end_date_obj);
            $days_left = $interval->days;

            if ($days_left > 0) {
                /* translators: %d: days left */
                return sprintf(_n('%d day left', '%d days left', $days_left, 'growfund'), $days_left);
            } elseif ($interval->h > 0) {
                /* translators: %d: interval */
                return sprintf(_n('%d hour left', '%d hours left', $interval->h, 'growfund'), $interval->h);
            } else {
                return __('Ending today', 'growfund');
            }
        } catch (Exception $error) {
            return __('Active', 'growfund');
        }
    }
}

$campaign = $campaign ?? null;
$variant = $variant ?? 'recommendation';

if (!$campaign) {
    return;
}

$progress = 0;
$time_left = __('Active', 'growfund');
$badge_content = '';
$badge_class = '';

if ($campaign->has_goal && !empty($campaign->goal_type)) {
    $progress = GoalDisplay::calculate_progress(
        $campaign->goal_type,
        $campaign->goal_amount,
        $campaign->fund_raised,
        $campaign->number_of_contributors ?? 0,
        $campaign->number_of_contributions ?? 0
    );
} else {
    $progress = (!empty($campaign->goal_amount) && is_numeric($campaign->goal_amount) && $campaign->goal_amount > 0 &&
        !empty($campaign->fund_raised) && is_numeric($campaign->fund_raised) && $campaign->fund_raised > 0)
        ? min(100, ($campaign->fund_raised / $campaign->goal_amount) * 100)
        : 0;
}

if ($variant === 'list' || $variant === 'featured') {
    $time_left = get_campaign_time_left($campaign->end_date ?? '');
    $badge_content = $time_left;
    $badge_class = 'growfund-time-badge';
} else {
    $funding = growfund_to_currency($campaign->fund_raised) . ' ' . __('raised', 'growfund');
    $time_left = get_campaign_time_left($campaign->end_date ?? '');
    $badge_content = $funding;
    $badge_class = 'growfund-funding-badge';
}

$card_class = 'growfund-campaign-card';

if ($variant === 'list') {
    $card_class .= ' growfund-campaign-card--list';
} elseif ($variant === 'featured') {
    $card_class .= ' growfund-campaign-card--featured';
}
?>

<div class="growfund-campaign-card-wrapper">
    <a href="<?php echo esc_url($campaign->url); ?>" class="<?php echo esc_attr($card_class); ?>">
        <div class="growfund-campaign-card__image">
            <?php if ($campaign->thumbnail) : ?>
                <?php
                growfund_renderer()
                    ->render('site.components.image', [
                        'src' => $campaign->thumbnail,
                        'alt' => $campaign->title
                    ]);
				?>
            <?php else : ?>
                <div class="growfund-campaign-card__placeholder">
                    <span><?php echo esc_html(substr($campaign->title, 0, 1)); ?></span>
                </div>
            <?php endif; ?>

            <div class="<?php echo esc_attr($badge_class); ?>"><?php echo esc_html($badge_content); ?></div>

            <?php
            if (!empty($campaign->id) && is_numeric($campaign->id)  && !growfund_user()->is_admin()) :
                if (growfund_user()->is_logged_in()) :
					?>
                    <?php
                    growfund_renderer()
                        ->render('site.components.button', [
                            'variant' => 'bookmark',
                            'class' => $campaign->is_bookmarked ? 'bookmarked' : '',
                            'ariaLabel' => $campaign->is_bookmarked ? __('Remove bookmark', 'growfund') : __('Bookmark campaign', 'growfund'),
                            'attributes' => [
                                'data-action' => 'bookmark_campaign_card',
                                'data-campaign-id' => $campaign->id,
                                'data-is-bookmarked' => $campaign->is_bookmarked ? 'true' : 'false'
                            ]
                        ]);
					?>
                <?php else : ?>
                    <?php
                    growfund_renderer()
                        ->render('site.components.button', [
                            'variant' => 'bookmark',
                            'ariaLabel' => __('Bookmark campaign', 'growfund'),
                            /* translators: %s: login url with campaign url */
                            'onclick' => sprintf('window.location.href = "%s"', growfund_login_url($campaign->url))

                        ]);
					?>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <div class="growfund-campaign-card__content">
            <div class="growfund-creator-info">
                <?php if (!empty($campaign->author_image)) : ?>
                    <?php
                    growfund_renderer()
                        ->render('site.components.image', [
                            'src' => $campaign->author_image,
                            'alt' => $campaign->author_name ?? __('Creator', 'growfund'),
                            'attributes' => ['class' => 'growfund-creator-avatar']
                        ]);
					?>
                <?php else : ?>
                    <div class="growfund-creator-avatar growfund-creator-avatar--placeholder">
                        <?php echo esc_html(substr(!empty($campaign->author_name) ? strtoupper($campaign->author_name) : 'A', 0, 1)); ?>
                    </div>
                <?php endif; ?>

                <span class="growfund-creator-name <?php echo $variant === 'list' ? 'growfund-creator-name--list' : ''; ?>">
                    <?php echo esc_html(!empty($campaign->author_name) ? $campaign->author_name : __('Anonymous', 'growfund')); ?>
                </span>

                <?php if ($variant === 'recommendation') : ?>
                    <span class="growfund-time-left"><?php echo esc_html($time_left); ?></span>
                <?php endif; ?>
            </div>

            <h3 class="growfund-campaign-title"><?php echo esc_html($campaign->title); ?></h3>

            <div class="growfund-funding-progress">
                <div class="growfund-progress-bar">
                    <div class="growfund-progress-fill" style="width: <?php echo esc_attr($progress); ?>%;"></div>
                </div>
                <div class="growfund-funding-amount">
                    <?php
                    if ($campaign->has_goal && !empty($campaign->goal_type)) {
                        echo esc_html(GoalDisplay::format_funding_amount_display(
                            $campaign->goal_type ?? null,
                            growfund_to_currency($campaign->fund_raised),
                            $campaign->number_of_contributors ?? null,
                            $campaign->number_of_contributions ?? null
                        ));
                    } else {
                        echo esc_html(growfund_to_currency($campaign->fund_raised)) . ' ' . esc_html__('raised', 'growfund');
                    }
                    ?>
                </div>
            </div>

            <p class="growfund-campaign-description"><?php echo esc_html($campaign->description ?? __('No description available.', 'growfund')); ?></p>
        </div>
    </a>
</div>