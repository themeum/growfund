<?php

namespace Growfund\Views\Components\Campaign\Tabs;

use Growfund\View;

defined('ABSPATH') || exit;

class CommentContent extends View {

    /** @var string */
    public $campaign_id;

    /** @var bool */
    public $has_faqs = false;

	protected function get_template_dir() {
		return 'site/components/campaign/tabs/';
	}

	protected function enqueue_styles() {
		wp_enqueue_style(
		'growfund-comment-tab-content-styles',
		GROWFUND_DIR_URL . 'resources/assets/site/styles/components/campaign/tabs/comment-tab-content.css',
		['growfund-main-styles'],
		GROWFUND_VERSION
		);
	}
	protected function enqueue_scripts() {
		wp_enqueue_script(
		'growfund-comment-tab-content',
		GROWFUND_DIR_URL . 'resources/assets/site/scripts/components/campaign/tabs/comment-tab-content.js',
		['growfund-core', 'growfund-comment-script'],
		GROWFUND_VERSION,
		true
		);
    }
}
