<?php
/**
 * @var Growfund\Views\Components\Donors\DonorItem $donor_item
 */

use Growfund\Constants\Contributor\DisplayOption;
use Growfund\Core\AppSettings;
use Growfund\Supports\Currency;
use Growfund\Supports\Date;

defined( 'ABSPATH' ) || exit;

$display_option = growfund_settings(AppSettings::CAMPAIGNS)->display_contributor_option();
?>

<div class="growfund-donor-list-item">
    <div class="growfund-donor-list-item-wrapper">
        <div class="growfund-donor-list-icon-container">
            <?php growfund_echo_safe_html($donor_item->get_svg_icon('assets/site/icon/heart-handshake.svg')); ?>
        </div>
        
        <div class="growfund-donor-list-details">
            <div class="growfund-donor-list-info-top">
                <?php if (in_array($display_option, [DisplayOption::SHOW_NAME, DisplayOption::SHOW_AMOUNT_AND_NAME], true)) : ?>
                    <span class="growfund-donor-list-name">
                        <?php printf('%s %s', esc_html($donor_item->donor->first_name), esc_html($donor_item->donor->last_name)); ?>
                    </span>
                <?php endif; ?>

                <div class="growfund-donor-list-info-bottom">
                    <?php if (in_array($display_option, [DisplayOption::SHOW_AMOUNT, DisplayOption::SHOW_AMOUNT_AND_NAME], true)) : ?>
                        <span class="growfund-donor-list-amount">
                            <?php echo esc_html(Currency::format($donor_item->donor->max_contribution_amount)); ?>
                        </span>
                        <span class="growfund-donor-list-dot">•</span>
                    <?php endif; ?>

                    <span class="growfund-donor-list-time">
                        <?php echo esc_html(Date::human_readable_time_diff($donor_item->donor->donated_at)); ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>