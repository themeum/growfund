<?php

namespace Growfund\Views\Components\UI;

use Growfund\View;     

defined('ABSPATH') || exit;

class Video extends View
{
    /** @var string */
    public $title;

    /** @var string */
    public $src;

    /** @var bool */
    public $autoplay = false;

    /** @var bool */
    public $muted = false;

    /** @var bool */
    public $loop = false;

    /** @var bool */
    public $controls = true;

    /** @var string|null */
    public $thumbnail_src;

    /** @var string|null */
    public $poster_src;

    /** @var string|null */
    protected $embed_url;

    const PLATFORM_YOUTUBE = 'youtube.com';
    const PLATFORM_YOUTUBE_SHORT = 'youtu.be';
    const YOUTUBE_EMBED_URL = 'https://www.youtube.com/embed/';
    const YOUTUBE_EMBED_PARAMS = '?rel=0&modestbranding=1&showinfo=0&iv_load_policy=3&fs=1&disablekb=0&autoplay=0&mute=0';

    const PLATFORM_VIMEO = 'vimeo.com';
    const VIMEO_EMBED_URL = 'https://player.vimeo.com/video/';

    protected function get_template_dir()
    {
        return 'site/components/ui';
    }

    protected function enqueue_scripts()
    {
        $script_url = GROWFUND_DIR_URL . 'resources/assets/site/scripts/components/ui/video.js';
        wp_enqueue_script('growfund-video-script', $script_url, ['growfund-core'], GROWFUND_VERSION, true);
    }
    protected function enqueue_styles()
    {
        $main_styles_url = GROWFUND_DIR_URL . 'resources/assets/site/styles/components/ui/video.css';

        wp_enqueue_style(
                'growfund-video-styles',
                $main_styles_url,
                ['growfund-main-styles'],
                GROWFUND_VERSION
            );
    }

    /**
     * Get the video type
     * @return 'direct' | 'youtube' | 'vimeo'
     */
    public function get_video_type($url)
	{
        $this->embed_url = $url;

		if (empty($url)) {
		    return 'direct';
		}

		$url_lower = strtolower($url);

		// Check for YouTube
		if (strpos($url_lower, static::PLATFORM_YOUTUBE) !== false || strpos($url_lower, static::PLATFORM_YOUTUBE_SHORT) !== false) {
			$video_id = '';

			if (strpos($url_lower, static::PLATFORM_YOUTUBE) !== false) {
				// Extract video ID from youtube.com URLs
				preg_match('/[?&]v=([^&]+)/', $url, $matches);
				$video_id = $matches[1] ?? '';
			} elseif (strpos($url_lower, static::PLATFORM_YOUTUBE_SHORT) !== false) {
				// Extract video ID from youtu.be URLs
				preg_match('/youtu\.be\/([^?&]+)/', $url, $matches);
				$video_id = $matches[1] ?? '';
			}

			if ($video_id) {
                $this->embed_url = static::YOUTUBE_EMBED_URL . $video_id . static::YOUTUBE_EMBED_PARAMS;
                return 'youtube';
			}
		}

		// Check for Vimeo
		if (strpos($url_lower, static::PLATFORM_VIMEO) !== false) {
			// Extract video ID from vimeo.com URLs
			preg_match('/vimeo\.com\/(\d+)/', $url, $matches);
			$video_id = $matches[1] ?? '';

			if ($video_id) {
                $this->embed_url = static::VIMEO_EMBED_URL . $video_id;
				return 'vimeo';
			}
		}

        return 'direct';
	}

    public function get_embed_url() {
        return $this->embed_url;
    }
}
