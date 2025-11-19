<?php

namespace Growfund\Shortcodes\Auth;

defined( 'ABSPATH' ) || exit;

use Growfund\Core\Shortcode;

class Register extends Shortcode
{
    protected $name = 'growfund_register';

    public function callback($attributes, string $content = '', string $shortcode_tag = '')
    {
        if (growfund_user()->is_logged_in()) {
            growfund_redirect(site_url());
        }

        $is_fundraiser = isset($attributes['user_type']) && $attributes['user_type'] === 'fundraiser';

        return growfund_renderer()->get_html('site.auth.register', [
			'is_shortcode' => true,
			'is_fundraiser' => $is_fundraiser,
			'redirect_to' => wp_get_referer()
		]);
    }
}
