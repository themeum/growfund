<?php

namespace Growfund\Views\Components\Auth;

use Growfund\View;

defined( 'ABSPATH' ) || exit;

class ResetPassword extends View {

    /** @var string */
    public $classname;

    public $username;

    public $token;

    protected function get_template_dir() {
        return 'site/components/auth';
    }

    protected function enqueue_styles() {
        $style_url = GROWFUND_DIR_URL . 'resources/assets/site/styles/components/auth/reset-password.css';
        wp_enqueue_style('growfund-reset-password-styles', $style_url, ['growfund-main-styles'], GROWFUND_VERSION);
    }
}
