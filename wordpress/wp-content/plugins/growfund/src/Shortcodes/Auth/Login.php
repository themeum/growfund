<?php

namespace Growfund\Shortcodes\Auth;

defined( 'ABSPATH' ) || exit;

use Growfund\Core\Shortcode;

class Login extends Shortcode
{
    protected $name = 'growfund_login';

    public function callback($attributes, string $content = '', string $shortcode_tag = '')
    {
        if (growfund_user()->is_logged_in()) {
            growfund_redirect(site_url());
        }
        
        return growfund_renderer()->get_html('site.auth.login', [
            'is_shortcode' => true,
            'redirect_to' => wp_get_referer(),
            'terms_and_conditions_url' => $attributes['terms_and_conditions_url'] ?? null,
            'privacy_url' => $attributes['privacy_url'] ?? null,
        ]);
    }
}
