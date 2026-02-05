<?php
/**
 * @var Growfund\Views\Components\CampaignUpdate\UpdateCard $update_card
 */

defined('ABSPATH') || exit;

use Growfund\Constants\DateTimeFormats;
use Growfund\Supports\Date;
use Growfund\Views\Components\Form\Button;

if (empty($update_card->update)) {
    return;
}
?>

<div class="growfund-update-tab-content-card" data-campaign-update-date="<?php echo esc_attr($update_card->update->created_at); ?>"> 
    <div class="growfund-update-tab-content-header">
        <span class="growfund-update-tab-content-date" data-growfund-datetime="<?php echo esc_attr($update_card->update->created_at); ?>">
            <?php 
                echo esc_html(Date::format($update_card->update->created_at, DateTimeFormats::HUMAN_READABLE_DAY_OF_MONTH)); 
            ?>
        </span>
        <h2 class="growfund-update-tab-content-title">
            <?php echo esc_html($update_card->update->title); ?>
        </h2>
    </div>

    <?php if (!empty($update_card->update->image['url'])) : ?>
        <div class="growfund-update-tab-content-image">
            <img src="<?php echo esc_url($update_card->update->image['url']); ?>" alt="<?php echo esc_attr($update_card->update->title); ?>">
        </div>
    <?php endif; ?>

    <div class="growfund-update-tab-content-content">
        <p>
            <?php
            echo(esc_html($update_card->update->description)); 
            ?>
        </p>
    </div>

    <div class="growfund-update-tab-content-author">
        <div class="growfund-update-tab-content-avatar">
            <?php 
            $growfund_avatar_data = $update_card->update->created_by_image;
            $growfund_avatar = growfund_user_avatar();

            if (is_array($growfund_avatar_data) && !empty($growfund_avatar_data['url'])) {
                $growfund_avatar = $growfund_avatar_data['url'];
            } elseif (is_string($growfund_avatar_data) && !empty($growfund_avatar_data)) {
                $growfund_avatar = $growfund_avatar_data;
            }

			?>
            <img src="<?php echo esc_url($growfund_avatar); ?>" alt="avatar">
        </div>
        <span><?php echo esc_html($update_card->update->created_by_name); ?></span>
    </div>

    <div class="growfund-update-tab-content-footer">
        <div class="growfund-update-tab-content-icons">
            <div class="growfund-update-tab-content-icons-part">
            <?php growfund_echo_safe_html($update_card->get_svg_icon('assets/site/icon/reply.svg')); ?>
            <span class="growfund-update-tab-content-reply-text"><?php echo (int) ($update_card->update->likes ?? 0); ?></span>
            </div>
        </div>

        <?php
        $growfund_button = new Button();
        $growfund_button->label = __('Read more', 'growfund');
        $growfund_button->classname = 'growfund-update-card-read-more';
        $growfund_button->id =  esc_attr($update_card->update->id);
       
        growfund_render($growfund_button);
        ?>
    </div>

</div>