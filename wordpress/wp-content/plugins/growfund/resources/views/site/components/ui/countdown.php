<?php

/** @var Growfund\Views\Components\UI\Countdown $countdown */

defined( 'ABSPATH' ) || exit;

?>

<div class="growfund-countdown" data-end-date="<?php echo esc_attr($countdown->end_date); ?>">
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