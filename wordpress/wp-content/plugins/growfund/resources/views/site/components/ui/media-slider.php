<?php 
/** @var Growfund\Views\Components\UI\MediaSlider $media_slider */

defined( 'ABSPATH' ) || exit;

use Growfund\Views\Components\UI\Image;
use Growfund\Views\Components\UI\Video;

$growfund_total_media_count = 0;

?>

<div class="growfund-media-slider">
    <div class="growfund-media-slider-container-wrapper">
        <div class="growfund-media-slider-container" >
            <?php if (empty($media_slider->images) && empty($media_slider->video) && empty($media_slider->video['url'])) : ?>
                <div class="growfund-media-slider-item">
                    <?php 
                        $growfund_image_view = new Image();
                        $growfund_image_view->classname = 'growfund-media-slider-image';
                        $growfund_image_view->alt = __('Campaign', 'growfund');
                        $growfund_image_view->src = growfund_site_placeholder_image_url(false);
                        $growfund_image_view->object_fit = 'cover';

                        growfund_render($growfund_image_view);
                    ?>
                </div>
            <?php else : ?>
                <?php if (!empty($media_slider->video) && !empty($media_slider->video['url'])) : ?>
                    <div class="growfund-media-slider-item">
                        <?php 
                            ++$growfund_total_media_count;

                            $growfund_video_component = new Video();
                            $growfund_video_component->src = $media_slider->video['url'];
                            $growfund_video_component->controls = false;
                            $growfund_video_component->thumbnail_src = $media_slider->video['poster']['url'] ?? null;
                            
                            growfund_render($growfund_video_component);
                        ?>
                    </div>
                <?php endif; ?>
                <?php if (!empty($media_slider->images) && is_array($media_slider->images)) : ?>
                    <?php foreach ($media_slider->images as $growfund_media_image) : ?>
                        <div class="growfund-media-slider-item">
                            <?php
                                ++$growfund_total_media_count;

                                $growfund_image_view = new Image();
                                $growfund_image_view->classname = 'growfund-media-slider-image';
                                $growfund_image_view->alt = $growfund_media_image['alt'] ?? __('Image', 'growfund');
                                $growfund_image_view->src = !empty($growfund_media_image['url']) ? $growfund_media_image['url'] : growfund_placeholder_image_url();

                                growfund_render($growfund_image_view);
                            ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        
        <!-- Prev / Next Button -->
        <?php if ($growfund_total_media_count > 1) : ?>
            <button class="growfund-media-slider-btn growfund-media-slider-btn-prev" disabled type="button">
                <svg width="18" height="15" viewBox="0 0 18 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M7.76568 0.234324C8.07812 0.54674 8.07812 1.05327 7.76568 1.36568L2.73138 6.4H16.8C17.2418 6.4 17.6 6.75818 17.6 7.2C17.6 7.64183 17.2418 8 16.8 8H2.73138L7.76568 13.0342C8.07812 13.3467 8.07812 13.8533 7.76568 14.1658C7.45327 14.4781 6.94674 14.4781 6.63432 14.1658L0.234324 7.76568C-0.078108 7.45327 -0.078108 6.94674 0.234324 6.63432L6.63432 0.234324C6.94674 -0.078108 7.45327 -0.078108 7.76568 0.234324Z" fill="#F5F5F5"/>
                </svg>

            </button>
            <button class="growfund-media-slider-btn growfund-media-slider-btn-next" type="button">
                <svg width="18" height="15" viewBox="0 0 18 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M9.83432 0.234324C10.1467 -0.078108 10.6533 -0.078108 10.9657 0.234324L17.3658 6.63432C17.6781 6.94674 17.6781 7.45327 17.3658 7.76568L10.9657 14.1658C10.6533 14.4781 10.1467 14.4781 9.83432 14.1658C9.52189 13.8533 9.52189 13.3467 9.83432 13.0342L14.8686 8H0.8C0.358176 8 0 7.64183 0 7.2C0 6.75818 0.358176 6.4 0.8 6.4H14.8686L9.83432 1.36568C9.52189 1.05327 9.52189 0.54674 9.83432 0.234324Z" fill="#F5F5F5"/>
                </svg>
            </button>
        <?php endif; ?>
    </div>

    <!-- Media Slider Thumbnails -->
    <?php if ($growfund_total_media_count > 1) : ?>
        <div class="growfund-media-slider-thumbnails">
            <?php if (!empty($media_slider->video) && !empty($media_slider->video['url'])) : ?>
                <div class="growfund-media-slider-thumb growfund-media-slider-video-thumb">
                    <img src="
                            <?php
                                echo !empty($media_slider->video['poster']) && !empty($media_slider->video['poster']['url']) 
                                    ? esc_url($media_slider->video['poster']['url']) 
                                    : esc_url(growfund_site_placeholder_image_url());
                            ?>
                        " 
                        alt="thumb" />
                    <div  class="growfund-video-play-icon">
                        <?php growfund_echo_safe_html($media_slider->get_svg_icon('assets/site/icon/play.svg')); ?>
                    </div>
                </div>
            <?php endif; ?>
            <?php if (!empty($media_slider->images) && is_array($media_slider->images)) : ?>
                <?php foreach ($media_slider->images as $growfund_media_image) : ?>
                    <div class="growfund-media-slider-thumb">
                        <?php 
                            $growfund_image_view = new Image();
                            $growfund_image_view->alt = $growfund_media_image['alt'] ?? __('thumb', 'growfund');
                            $growfund_image_view->src = !empty($growfund_media_image['url']) 
                                ? $growfund_media_image['url']
                                : growfund_site_placeholder_image_url();

                            growfund_render($growfund_image_view);
                        ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
