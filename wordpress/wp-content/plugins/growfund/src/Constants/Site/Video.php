<?php

namespace Growfund\Constants\Site;

defined( 'ABSPATH' ) || exit;

use Growfund\Traits\HasConstants;

/**
 * Site Video Constants
 * 
 * Constants used in site video-related functionality
 * 
 * @since 1.0.0
 */
class Video
{
    use HasConstants;

    /**
     * Video types
     */
    const TYPE_DIRECT = 'direct';
    const TYPE_YOUTUBE = 'youtube';
    const TYPE_VIMEO = 'vimeo';

    /**
     * Video platforms
     */
    const PLATFORM_YOUTUBE = 'youtube.com';
    const PLATFORM_YOUTUBE_SHORT = 'youtu.be';
    const PLATFORM_VIMEO = 'vimeo.com';

    /**
     * Video embed URLs
     */
    const YOUTUBE_EMBED_URL = 'https://www.youtube.com/embed/';
    const VIMEO_EMBED_URL = 'https://player.vimeo.com/video/';

    /**
     * YouTube embed parameters for better accessibility
     */
    const YOUTUBE_EMBED_PARAMS = '?rel=0&modestbranding=1&showinfo=0&iv_load_policy=3&fs=1&disablekb=0&autoplay=0&mute=0';

    /**
     * Video file types
     */
    const VIDEO_TYPE_MP4 = 'video/mp4';
    const VIDEO_TYPE_WEBM = 'video/webm';
    const VIDEO_TYPE_OGG = 'video/ogg';

    /**
     * Default video attributes
     */
    const DEFAULT_CLASS = 'growfund-video';
    const DEFAULT_CONTROLS = true;
    const DEFAULT_AUTOPLAY = false;
    const DEFAULT_MUTED = false;
    const DEFAULT_LOOP = false;

    /**
     * Video ID prefix
     */
    const VIDEO_ID_PREFIX = 'growfund-video-';

    /**
     * Video container data attribute
     */
    const VIDEO_CONTAINER_DATA_ATTR = 'data-video-container';

    /**
     * Video play overlay data attribute
     */
    const VIDEO_PLAY_OVERLAY_DATA_ATTR = 'data-play-overlay';

    /**
     * Video play button class
     */
    const VIDEO_PLAY_BTN_CLASS = 'growfund-video-play-btn';

    /**
     * Video play icon class
     */
    const VIDEO_PLAY_ICON_CLASS = 'growfund-video-play-icon';

    /**
     * Video container class
     */
    const VIDEO_CONTAINER_CLASS = 'growfund-video-container';

    /**
     * Video play overlay class
     */
    const VIDEO_PLAY_OVERLAY_CLASS = 'growfund-video-play-overlay';

    /**
     * YouTube iframe class for accessibility
     */
    const YOUTUBE_IFRAME_CLASS = 'growfund-youtube-iframe';

    /**
     * Hidden class for CSS
     */
    const HIDDEN_CLASS = 'hidden';

    /**
     * Video preload attribute value
     */
    const VIDEO_PRELOAD_METADATA = 'metadata';

    /**
     * YouTube iframe attributes
     */
    const YOUTUBE_ALLOW_ATTRIBUTES = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share';

    /**
     * Vimeo iframe attributes
     */
    const VIMEO_ALLOW_ATTRIBUTES = 'autoplay; fullscreen; picture-in-picture';

    /**
     * Default video titles for accessibility
     */
    const DEFAULT_YOUTUBE_TITLE = 'YouTube video';
    const DEFAULT_VIMEO_TITLE = 'Vimeo video';
    const DEFAULT_VIDEO_FALLBACK_MESSAGE = 'Your browser does not support the video tag.';

    /**
     * Play button aria label
     */
    const PLAY_BUTTON_ARIA_LABEL = 'Play video';

    /**
     * Default video player aria label
     */
    const DEFAULT_VIDEO_PLAYER_ARIA_LABEL = 'Video player';

    /**
     * Icon size for play button
     */
    const PLAY_ICON_SIZE = '64';

    /**
     * Icon name for play button
     */
    const PLAY_ICON_NAME = 'play-video';
}
