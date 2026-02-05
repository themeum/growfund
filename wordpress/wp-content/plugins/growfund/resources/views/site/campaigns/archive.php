<?php

defined( 'ABSPATH' ) || exit;

use Growfund\Supports\Template;
use Growfund\View;

// Before calling growfund_get_header() we have to get the campaign archive content, otherwise assets will not be loaded
$growfund_html = Template::get_campaign_archive_content();

growfund_get_header();

echo wp_kses('<div class="growfund-page-container">' . $growfund_html . '</div>', View::get_allowed_html_tags());

growfund_get_footer();
