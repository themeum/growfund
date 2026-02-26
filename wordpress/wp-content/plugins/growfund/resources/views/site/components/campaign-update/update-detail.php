<?php

/** @var \Growfund\Views\Components\CampaignUpdate\UpdateDetail $update_detail */

use Growfund\Constants\DateTimeFormats;
use Growfund\Views\Components\Comments\CommentContainer;
use Growfund\Views\Components\Form\Button;
use Growfund\PostTypes\CampaignPost;
use Growfund\Supports\Date;

defined( 'ABSPATH' ) || exit;


$growfund_main_image_url = !empty($update_detail->update->image['url']) ? $update_detail->update->image['url'] : '';
$growfund_author_avatar  = !empty($update_detail->update->created_by_image['url']) ? $update_detail->update->created_by_image['url'] : growfund_user_avatar();
?>

<div class="growfund-update-detail-wrapper">

    <div class="growfund-update-detail-header">
        <?php
        $growfund_updates_button = new Button();
        $growfund_updates_button->label = "All updates";
        $growfund_updates_button->classname = "growfund-update-detail-button";
        $growfund_updates_button->id = "growfund-update-detail-button";
        $growfund_updates_button->svg_icon = "assets/site/icon/arrow-left.svg";
        $growfund_updates_button->icon_position = "left";
        growfund_render($growfund_updates_button);
        ?>
        <span class="growfund-update-detail-update-no">UPDATE #<?php echo esc_html($update_detail->update->id); ?></span>
    </div>

    <div class="growfund-update-detail-title-wrapper">
        <span class="growfund-update-detail-title"><?php echo esc_html($update_detail->update->title); ?></span>
        <span class="growfund-update-detail-subtitle"><?php echo esc_html($update_detail->update->description); ?></span>
    </div>

    <div class="growfund-update-detail-creator-share-wrapper">
        <div class="growfund-update-detail-author">
            <div class="growfund-update-detail-avatar">
                <img src="<?php echo esc_url($growfund_author_avatar); ?>" alt="<?php echo esc_attr($update_detail->update->created_by_name); ?>">
            </div>
            <div class="growfund-update-detail-author-main">
                <div class="growfund-update-detail-author-wrapper">
                    <span class="growfund-update-detail-author-name" id="growfund_update_detail_author_name">
                        <?php echo esc_html($update_detail->update->created_by_name); ?>
                    </span>
                    <span class="growfund-update-detail-author-designation" id="growfund_update_detail_author_role">
                        <?php echo esc_html($update_detail->update->created_by_role); ?>
                    </span>
                </div>
                <span class="growfund-update-detail-create-date">
                    <?php echo esc_html(Date::format($update_detail->update->created_at, DateTimeFormats::HUMAN_READABLE_DATE)); ?>
                </span>
            </div>
        </div>

        <div class="growfund-update-detail-social-share"></div>
    </div>

    <?php if ($growfund_main_image_url) : ?>
    <div class="growfund-update-detail-image-container">
        <img src="<?php echo esc_url($growfund_main_image_url); ?>" alt="<?php echo esc_attr($update_detail->update->title); ?>">
    </div>
    <?php endif; ?>

    <div class="growfund-update-detail-prev-count-wrapper">

        <div class="growfund-update-detail-button-wrapper">
            <?php
            $growfund_prev_button = new Button();
            $growfund_prev_button->label = "Previous";
            $growfund_prev_button->classname = "growfund-update-detail-prev-button";
            $growfund_prev_button->id = "growfund-update-detail-prev-button";
            $growfund_prev_button->svg_icon = "assets/site/icon/arrow-left.svg";
            $growfund_prev_button->icon_position = "left";
            growfund_render($growfund_prev_button);

            $growfund_next_button = new Button();
            $growfund_next_button->label = "Next";
            $growfund_next_button->classname = "growfund-update-detail-next-button";
            $growfund_next_button->id = "growfund-update-detail-next-button";
            $growfund_next_button->svg_icon = "assets/site/icon/arrow-right.svg";
            $growfund_next_button->icon_position = "right";
            growfund_render($growfund_next_button);
            ?>
        </div>
    </div>

    <span class="growfund-update-detail-comment-title">Comments</span>

    <?php if (!is_user_logged_in()) : ?>
    <div class="growfund-update-detail-login">
        Only backers can post comments. <span class="growfund-update-detail-login-text">Login</span>
    </div>
    <?php endif; ?>

    <div class="growfund-update-detail-comment">
        <?php
        $growfund_comment_container = new CommentContainer();
        $growfund_comment_container->id = "growfund_update_detail_comment_container";
        $growfund_comment_container->comment_type = CampaignPost::COMMENT_TYPE;
        $growfund_comment_container->post_id = $update_detail->update->id; 
        growfund_render($growfund_comment_container);
        ?>
    </div>

</div>