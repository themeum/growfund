<?php

namespace Growfund\Shortcodes;

defined( 'ABSPATH' ) || exit;

use Growfund\Core\Shortcode;
use Growfund\Supports\Template;

class Campaigns extends Shortcode
{
    protected $name = 'growfund_campaigns';

    public function callback($attributes, string $content = '', string $shortcode_tag = '')
    {
        return '<div class="growfund-page-container">' . Template::get_campaign_archive_content($content) . '</div>';
    }
}
