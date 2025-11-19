<?php

defined( 'ABSPATH' ) || exit;

/**
 * Update Item Component
 * 
 * @var CampaignUpdateDTO|array $data
 */

// Ensure we have valid data
if (empty($data)) {
    return;
}

// Convert data to object if it's an array
if (is_array($data)) {
    $data = (object) $data;
}

// Convert date string to timestamp for data attribute
$timestamp = strtotime($data->update_date ?? $data->date ?? '');
$data_date = $timestamp ? wp_date('Y-m-d', $timestamp) : wp_date('Y-m-d');

$position = $data->update_position ?? $data->position ?? 0;
?>

<div class="growfund-card growfund-update-item" data-update-id="<?php echo esc_attr($data->update_id ?? $data->id ?? ''); ?>" data-date="<?php echo esc_attr($data_date); ?>" data-position="<?php echo esc_attr($position); ?>">
    <div class="growfund-update-item__header">
        <div class="growfund-update-item__meta">
            <span class="growfund-update-date" data-growfund-datetime="<?php echo esc_attr($data->update_date ?? ''); ?>"><?php echo esc_html($data->update_date ?? $data->date ?? ''); ?></span>
        </div>
        <h3 class="growfund-update-item__title"><?php echo esc_html($data->update_title ?? $data->title ?? ''); ?></h3>
    </div>

    <?php if (!empty($data->update_image ?? $data->image)) : ?>
        <div class="growfund-update-item__image">
            <?php
            growfund_renderer()
                ->render('site.components.image', [
                    'src' => $data->update_image ?? $data->image,
                    'alt' => $data->update_image_alt ?? $data->image_alt ?? ''
                ]);
			?>
        </div>
    <?php endif; ?>

    <div class="growfund-update-item__content">
        <p class="growfund-update-item__content-text"><?php echo esc_html($data->update_content_preview ?? $data->content_preview ?? ''); ?></p>
    </div>

    <div class="growfund-update-creator">
        <div class="growfund-update-creator__avatar-wrapper">
            <?php
            $creator_data = $data->update_creator ?? $data->creator ?? [];
            $creator_avatar = '';
            $creator_name = '';

            if (is_array($creator_data) && !empty($creator_data)) {
                $creator_avatar = empty($creator_data['avatar']) ? growfund_user_avatar() : $creator_data['avatar'];
                $creator_name = $creator_data['name'] ?? '';
            }
            ?>
            <?php
            growfund_renderer()
                ->render('site.components.image', [
                    'src' => esc_url($creator_avatar),
                    'alt' => esc_html($creator_name),
                    'attributes' => ['class' => 'growfund-update-creator__avatar']
                ]);
			?>
        </div>
        <div class="growfund-update-creator__info">
            <span class="growfund-update-creator__name"><?php echo esc_html($creator_name); ?></span>
        </div>
    </div>

    <div class="growfund-update-item__footer">
        <div class="growfund-update-actions">
            <?php
            $stats_data = $data->update_stats ?? $data->stats ?? [];
            $comments_count = 0;
            $likes_count = 0;

            if (is_array($stats_data) && !empty($stats_data)) {
                $comments_count = $stats_data['comments'] ?? 0;
                $likes_count = $stats_data['likes'] ?? 0;
            }
            ?>
            <a class="growfund-action-btn" data-action="comment">
                <?php
                growfund_renderer()
                    ->render('site.components.icon', [
                        'name' => 'reply',
                        'size' => 'sm'
                    ]);
				?>
                <span><?php echo esc_html($comments_count); ?></span>
            </a>
        </div>
        <div class="growfund-read-more-wrapper">
            <?php
            growfund_renderer()
                ->render('site.components.button', [
                    'text' => esc_html__('Read more', 'growfund'),
                    'class' => 'growfund-btn--gray',
                    'attributes' => [
                        'data-update-id' => esc_attr($data->update_id ?? $data->id ?? '')
                    ]
                ]);
			?>
        </div>
    </div>
</div>