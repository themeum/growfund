<?php

namespace Growfund\Shortcodes;

defined( 'ABSPATH' ) || exit;

use Growfund\Core\Shortcode;

class Thumbnail extends Shortcode
{
    protected $name = 'growfund_campaign_thumbnail';

    public function callback($attributes, string $content = '', string $shortcode_tag = '')
    {
        $attributes = shortcode_atts([
            'src' => '#',
        ], $attributes);

        $source = esc_url($attributes['src']);

        return growfund_renderer()->get_html('site.components.thumbnail', ['src' => $source]);
    }
}
