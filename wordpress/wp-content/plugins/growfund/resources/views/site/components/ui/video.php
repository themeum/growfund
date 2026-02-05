<?php 
/**
 * @var Growfund\Views\Components\UI\Video $video
 */

defined( 'ABSPATH' ) || exit;

$growfund_video_id = 'growfund-video-' . uniqid();
/** @var 'direct' | 'youtube' | 'vimeo' */
$growfund_video_type = $video->get_video_type($video->src);
?>


<div 
    class="growfund-video-container" 
    data-video-container="<?php echo esc_attr($growfund_video_id); ?>" 
    role="region" 
    aria-label="<?php echo esc_attr($video->title ? $video->title : __('Video player', 'growfund')); ?>"
>
    <?php if ($growfund_video_type === 'direct') : ?>
        <!-- Play Button Overlay for direct videos -->
        <?php if (!$video->autoplay) : ?>
            <?php if (!empty($video->thumbnail_src)) : ?>
                <div class="growfund-video-thumb">
                    <img src="<?php echo esc_url($video->thumbnail_src); ?>" alt="<?php echo esc_attr__('thumbnail', 'growfund'); ?>" />
                </div>
            <?php endif; ?>
            <div class="growfund-video-play-overlay" data-play-overlay="<?php echo esc_attr($growfund_video_id); ?>">
                <button type="button" class="growfund-video-play-btn" aria-label="<?php echo esc_attr__('Play video', 'growfund'); ?>">
                    <div  class="growfund-video-play-icon">
                        <?php growfund_echo_safe_html($video->get_svg_icon('assets/site/icon/play.svg')); ?>
                    </div>
                </button>
            </div>
        <?php endif; ?>

        <!-- Video Element for direct video files -->
        <video 
            id="<?php echo esc_attr($growfund_video_id); ?>"
            class="growfund-video growfund-video-hidden"
            <?php 
            echo $video->autoplay ? esc_attr(' autoplay'): ''; 
            echo $video->muted ? esc_attr(' muted') : ''; 
            echo $video->loop ? esc_attr(' loop') : '';

            if ($video->controls && !$video->autoplay) {
                echo esc_attr(' controls');
                echo esc_attr(' preload="metadata"');
            } 

            echo $video->poster_src ? esc_attr(' poster="' . esc_url($video->poster_src) . '"') : '';
            ?>
        >
            <source src="<?php echo esc_url($video->src); ?>" type="video/mp4">
            <source src="<?php echo esc_url($video->src); ?>" type="video/webm">
            <source src="<?php echo esc_url($video->src); ?>" type="video/ogg">
            <p><?php echo esc_html($video->title ? $video->title : __('Your browser does not support the video tag.', 'growfund')); ?></p>
        </video>

    <?php elseif ($growfund_video_type === 'youtube') : ?>
        <!-- YouTube iframe -->
        <iframe
            id="<?php echo esc_attr($growfund_video_id); ?>"
            class="growfund-youtube-iframe"
            src="<?php echo esc_url($video->get_embed_url()); ?>"
            frameborder="0"
            allowfullscreen
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
            title="<?php echo esc_attr($video->title ? $video->title: __('YouTube video', 'growfund')); ?>"
            loading="lazy"
            importance="high"
        ></iframe>

    <?php elseif ($growfund_video_type === 'vimeo') : ?>
        <!-- Vimeo iframe -->
        <iframe
            id="<?php echo esc_attr($growfund_video_id); ?>"
            src="<?php echo esc_url($video->get_embed_url()); ?>"
            frameborder="0"
            allowfullscreen
            allow="autoplay; fullscreen; picture-in-picture"
            title="<?php echo esc_attr($video->title ? $video->title : __('Vimeo video', 'growfund')); ?>"
        ></iframe>

    <?php endif; ?>
</div>