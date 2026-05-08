<?php

namespace Growfund\Shortcodes\Auth;

defined( 'ABSPATH' ) || exit;

use Growfund\Core\Shortcode;
use Growfund\Views\Components\Auth\Login as AuthLogin;

class Login extends Shortcode
{
    protected $name = 'growfund_login';

    public function callback($attributes, string $content = '', string $shortcode_tag = '')
    {
        if (growfund_user()->is_logged_in()) {
            growfund_redirect(growfund_user_dashboard_url());
        }
        
        $login_page = new AuthLogin();

        return growfund_get_html($login_page);
    }
}
