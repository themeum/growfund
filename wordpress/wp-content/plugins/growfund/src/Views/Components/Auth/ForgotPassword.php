<?php

namespace Growfund\Views\Components\Auth;

use Growfund\View;

defined( 'ABSPATH' ) || exit;

class ForgotPassword extends View {
    
    /** @var string */
    public $classname;

    protected function get_template_dir() {
        return 'site/components/auth';
    }

    protected function enqueue_styles() {
        $style_url = GROWFUND_DIR_URL . 'resources/assets/site/styles/components/auth/forgot-password.css';
        wp_enqueue_style('growfund-forgot-password-styles', $style_url, ['growfund-main-styles'], GROWFUND_VERSION);
    }
}
