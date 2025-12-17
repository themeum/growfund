<?php

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\HookNames;

/**
 * Countdown Component
 * Displays a live countdown timer that updates every second
 * 
 * @param string $end_date - Campaign end date in ISO format
 * @param string $campaign_id - Campaign ID for unique identifier
 */

$end_date = $end_date ?? '';
$campaign_id = $campaign_id ?? '';

if (empty($end_date)) {
    return;
}

$countdown_id = 'growfund-countdown-' . ($campaign_id ? $campaign_id : uniqid());
$countdown_inline_js = "
    document.addEventListener('DOMContentLoaded', function() {
        const countdownElement = document.getElementById('" . esc_js($countdown_id) . "');
        if (countdownElement) {
            initializeCountdown(countdownElement);
        }
    });
";

growfund_add_inline_script(HookNames::WP_ENQUEUE_SCRIPT, $countdown_inline_js);

?>

<div class="growfund-countdown" id="<?php echo esc_attr($countdown_id); ?>" data-end-date="<?php echo esc_attr($end_date); ?>">
    <div class="growfund-countdown__item">
        <div class="growfund-countdown__number" data-unit="days">--</div>
        <div class="growfund-countdown__label"><?php esc_html_e('Days', 'growfund'); ?></div>
    </div>
    <div class="growfund-countdown__item">
        <div class="growfund-countdown__number" data-unit="hours">--</div>
        <div class="growfund-countdown__label"><?php esc_html_e('Hours', 'growfund'); ?></div>
    </div>
    <div class="growfund-countdown__item">
        <div class="growfund-countdown__number" data-unit="minutes">--</div>
        <div class="growfund-countdown__label"><?php esc_html_e('Minutes', 'growfund'); ?></div>
    </div>
    <div class="growfund-countdown__item">
        <div class="growfund-countdown__number" data-unit="seconds">--</div>
        <div class="growfund-countdown__label"><?php esc_html_e('Seconds', 'growfund'); ?></div>
    </div>
</div>