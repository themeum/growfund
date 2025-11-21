<?php

defined( 'ABSPATH' ) || exit;

/**
 * Video Component
 * Flexible video component that renders video URLs with controls and play button overlay
 * Supports direct video files (MP4, WebM, OGG), YouTube, and Vimeo URLs
 * 
 * @param string $src - Video source URL
 * @param string $poster - Poster image URL (optional)
 * @param string $title - Video title for accessibility
 * @param array $attributes - Additional HTML attributes (class, id, data-*, etc.)
 * @param bool $autoplay - Whether to autoplay the video (default: false)
 * @param bool $muted - Whether to mute the video (default: false)
 * @param bool $loop - Whether to loop the video (default: false)
 * @param bool $controls - Whether to show video controls (default: true)
 */

use Growfund\Constants\Site\Video as VideoConstants;

// The renderer uses extract() so these variables are already available
$src = $src ?? '';
$thumbnail = $thumbnail ?? '';
$poster = $poster ?? '';
$title = $title ?? ''; // phpcs:ignore --  intentionally used
$attributes = $attributes ?? [];
$autoplay = $autoplay ?? VideoConstants::DEFAULT_AUTOPLAY;
$muted = $muted ?? VideoConstants::DEFAULT_MUTED;
$loop = $loop ?? VideoConstants::DEFAULT_LOOP;
$controls = $controls ?? VideoConstants::DEFAULT_CONTROLS;

// Generate unique ID for this video instance
$video_id = VideoConstants::VIDEO_ID_PREFIX . uniqid();

// Set default classes
$defaultClass = VideoConstants::DEFAULT_CLASS;
if (isset($attributes['class'])) {
    $attributes['class'] = $defaultClass . ' ' . $attributes['class'];
} else {
    $attributes['class'] = $defaultClass;
}

// Add the unique ID to attributes
$attributes['id'] = $video_id;

// Build attributes string
$attributeString = '';
foreach ($attributes as $key => $value) {
    $attributeString .= ' ' . esc_attr($key) . '="' . esc_attr($value) . '"';
}

// Detect video type and get embed URL
function detect_video_type($url)
{
    if (empty($url)) {
        return [
			'type' => VideoConstants::TYPE_DIRECT,
			'embed_url' => $url
		];
    }

    $url_lower = strtolower($url);

    // Check for YouTube
    if (strpos($url_lower, VideoConstants::PLATFORM_YOUTUBE) !== false || strpos($url_lower, VideoConstants::PLATFORM_YOUTUBE_SHORT) !== false) {
        $video_id = '';

        if (strpos($url_lower, VideoConstants::PLATFORM_YOUTUBE) !== false) {
            // Extract video ID from youtube.com URLs
            preg_match('/[?&]v=([^&]+)/', $url, $matches);
            $video_id = $matches[1] ?? '';
        } elseif (strpos($url_lower, VideoConstants::PLATFORM_YOUTUBE_SHORT) !== false) {
            // Extract video ID from youtu.be URLs
            preg_match('/youtu\.be\/([^?&]+)/', $url, $matches);
            $video_id = $matches[1] ?? '';
        }

        if ($video_id) {
            return [
                'type' => VideoConstants::TYPE_YOUTUBE,
                'embed_url' => VideoConstants::YOUTUBE_EMBED_URL . $video_id . VideoConstants::YOUTUBE_EMBED_PARAMS,
                'video_id' => $video_id
            ];
        }
    }

    // Check for Vimeo
    if (strpos($url_lower, VideoConstants::PLATFORM_VIMEO) !== false) {
        // Extract video ID from vimeo.com URLs
        preg_match('/vimeo\.com\/(\d+)/', $url, $matches);
        $video_id = $matches[1] ?? '';

        if ($video_id) {
            return [
                'type' => VideoConstants::TYPE_VIMEO,
                'embed_url' => VideoConstants::VIMEO_EMBED_URL . $video_id,
                'video_id' => $video_id
            ];
        }
    }

    // Default to direct video file
    return [
		'type' => VideoConstants::TYPE_DIRECT,
		'embed_url' => $url
	];
}

$video_info = detect_video_type($src);
$video_type = $video_info['type'];
$embed_url = $video_info['embed_url'];

// Add video-specific attributes for direct videos
$videoAttributes = '';
if ($video_type === VideoConstants::TYPE_DIRECT) {
    if ($autoplay) {
		$videoAttributes .= ' autoplay';
    }
    if ($muted) {
		$videoAttributes .= ' muted';
    }
    if ($loop) {
		$videoAttributes .= ' loop';
    }
    // Initially hide controls - they'll show when playing
    if ($controls && !$autoplay) {
		$videoAttributes .= ' controls preload="' . VideoConstants::VIDEO_PRELOAD_METADATA . '"';
    }
    if (!empty($poster)) {
		$videoAttributes .= ' poster="' . esc_url($poster) . '"';
    }
}
?>

<div class="<?php echo esc_attr(VideoConstants::VIDEO_CONTAINER_CLASS); ?>" <?php printf('%s="%s"', esc_html(VideoConstants::VIDEO_CONTAINER_DATA_ATTR), esc_attr($video_id)); ?> role="region" aria-label="<?php echo esc_attr($title ? $title: VideoConstants::DEFAULT_VIDEO_PLAYER_ARIA_LABEL); ?>">
    <?php if ($video_type === VideoConstants::TYPE_DIRECT) : ?>
        <!-- Play Button Overlay for direct videos -->
        <?php if (!$autoplay) : ?>
            <?php if (!empty($thumbnail)) : ?>
                <div class="growfund-video-thumb">
                    <img src="<?php echo esc_url($thumbnail); ?>" alt="Video thumbnail" />
                </div>
            <?php endif; ?>
            <div class="<?php echo esc_attr(VideoConstants::VIDEO_PLAY_OVERLAY_CLASS); ?>" <?php printf('%s="%s"', esc_html(VideoConstants::VIDEO_PLAY_OVERLAY_DATA_ATTR), esc_attr($video_id)); ?>>
                <button class="<?php echo esc_attr(VideoConstants::VIDEO_PLAY_BTN_CLASS); ?>" aria-label="<?php echo esc_attr(VideoConstants::PLAY_BUTTON_ARIA_LABEL); ?>">
                    <?php
                    growfund_renderer()
                        ->render('site.components.icon', [
                            'name' => VideoConstants::PLAY_ICON_NAME,
                            'size' => VideoConstants::PLAY_ICON_SIZE,
                            'attributes' => [
                                'class' => VideoConstants::VIDEO_PLAY_ICON_CLASS
                            ]
                        ]);
					?>
                </button>
            </div>
        <?php endif; ?>

        <!-- Video Element for direct video files -->
        <video<?php echo $attributeString; // phpcs:ignore -- already escaped ?><?php echo $videoAttributes;  // phpcs:ignore -- already escaped ?>>
            <source src="<?php echo esc_url($src); ?>" type="<?php echo esc_attr(VideoConstants::VIDEO_TYPE_MP4); ?>">
            <source src="<?php echo esc_url($src); ?>" type="<?php echo esc_attr(VideoConstants::VIDEO_TYPE_WEBM); ?>">
            <source src="<?php echo esc_url($src); ?>" type="<?php echo esc_attr(VideoConstants::VIDEO_TYPE_OGG); ?>">
            <p><?php echo esc_html($title ? $title : VideoConstants::DEFAULT_VIDEO_FALLBACK_MESSAGE); ?></p>
            </video>

        <?php elseif ($video_type === VideoConstants::TYPE_YOUTUBE) : ?>
            <!-- YouTube iframe -->
            <iframe
                <?php echo $attributeString;  // phpcs:ignore -- already escaped ?>
                class="<?php echo esc_attr(VideoConstants::YOUTUBE_IFRAME_CLASS); ?>"
                src="<?php echo esc_url($embed_url); ?>"
                frameborder="0"
                allowfullscreen
                allow="<?php echo esc_attr(VideoConstants::YOUTUBE_ALLOW_ATTRIBUTES); ?>"
                title="<?php echo esc_attr($title ? $title: VideoConstants::DEFAULT_YOUTUBE_TITLE); ?>"
                loading="lazy"
                importance="high"></iframe>

        <?php elseif ($video_type === VideoConstants::TYPE_VIMEO) : ?>
            <!-- Vimeo iframe -->
            <iframe
                <?php echo $attributeString;  // phpcs:ignore -- already escaped ?>
                src="<?php echo esc_url($embed_url); ?>"
                frameborder="0"
                allowfullscreen
                allow="<?php echo esc_attr(VideoConstants::VIMEO_ALLOW_ATTRIBUTES); ?>"
                title="<?php echo esc_attr($title ? $title : VideoConstants::DEFAULT_VIMEO_TITLE); ?>"></iframe>

        <?php endif; ?>
</div>

<?php if ($video_type === VideoConstants::TYPE_DIRECT) : ?>
    <!-- Video JavaScript functionality is handled by video.js -->
<?php endif; ?>