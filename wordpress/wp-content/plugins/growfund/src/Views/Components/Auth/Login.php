<?php

namespace Growfund\Views\Components\Auth;

use Growfund\View;

defined( 'ABSPATH' ) || exit;

class Login extends View {

    /** @var string */
    public $classname;

    protected function get_template_dir() {
        return 'site/components/auth';
    }

    protected function enqueue_styles() {
        $main_styles_url = GROWFUND_DIR_URL . 'resources/assets/site/styles/components/auth/login.css';

        wp_enqueue_style(
            'growfund-login-styles',
            $main_styles_url,
            ['growfund-main-styles'],
            GROWFUND_VERSION
        );
    }
}
