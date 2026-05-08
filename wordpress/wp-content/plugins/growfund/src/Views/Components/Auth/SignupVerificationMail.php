<?php

namespace Growfund\Views\Components\Auth;

use Growfund\View;

defined('ABSPATH') || exit;

class SignupVerificationMail extends View {
    
    /** @var string */
    public $email;

    /** @var string */
    public $header_icon;

    protected function get_template_dir() {
        return 'site/components/auth';
    }

	protected function enqueue_styles()
    {
        $main_styles_url = GROWFUND_DIR_URL . 'resources/assets/site/styles/components/auth/signup-verification-mail.css';

        wp_enqueue_style(
			'growfund-signup-styles',
			$main_styles_url,
			['growfund-main-styles'],
			GROWFUND_VERSION
        );
    }

    public function get_header_icon() {
        return $this->header_icon ? $this->get_svg_icon($this->header_icon) : '';
    }
}
