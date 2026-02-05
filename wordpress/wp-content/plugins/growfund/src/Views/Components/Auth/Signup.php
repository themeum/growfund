<?php

namespace Growfund\Views\Components\Auth;

use Growfund\View;

defined( 'ABSPATH' ) || exit;

class Signup extends View {

    /** @var string -- 'regular' or 'fundraiser' */
    public $user_type = 'regular';

    protected function get_template_dir() {
        return 'site/components/auth';
    }

    protected function enqueue_styles()
    {
        $main_styles_url = GROWFUND_DIR_URL . 'resources/assets/site/styles/components/auth/signup.css';

        wp_enqueue_style(
            'growfund-signup-styles',
            $main_styles_url,
            ['growfund-main-styles'],
            GROWFUND_VERSION
        );
    }
}
