<?php

namespace Growfund\Shortcodes\Auth;

defined( 'ABSPATH' ) || exit;

use Growfund\Core\Shortcode;
use Growfund\Views\Components\Auth\Signup;

class Register extends Shortcode
{
    protected $name = 'growfund_register';

    public function callback($attributes, string $content = '', string $shortcode_tag = '')
    {
        if (growfund_user()->is_logged_in()) {
            growfund_redirect(growfund_user_dashboard_url());
        }

        $signup_page = new Signup();
        $signup_page->user_type = $attributes['user_type'] ?? 'regular';
        
        return growfund_get_html($signup_page);
    }
}
