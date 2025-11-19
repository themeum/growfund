<?php

defined( 'ABSPATH' ) || exit;

use Growfund\Supports\Template;

growfund_get_header();

echo '<div class="growfund-page-container">' . Template::get_campaign_archive_content() . '</div>'; // phpcs:ignore -- already escaped

growfund_get_footer();
