<?php

namespace Growfund\Views\Components\Auth;

use Growfund\View;

defined( 'ABSPATH' ) || exit;

class ResetLink extends View {

    /** @var string */
    public $user_email = '';

    protected function get_template_dir() {
        return 'site/components/auth';
    }

    protected function enqueue_styles() {
        $style_url = GROWFUND_DIR_URL . 'resources/assets/site/styles/components/auth/reset-link.css';
        wp_enqueue_style('growfund-reset-link-styles', $style_url, ['growfund-main-styles'], GROWFUND_VERSION);
    }
}
