<?php

namespace Growfund\Views\Components\Auth;

use Growfund\View;

defined( 'ABSPATH' ) || exit;

class Login extends View {

    /** @var string */
    public $classname;

    /** @var bool */
    public $is_verfication_mail_sent = false;

    /** @var string */
    public $verification_email;

    public function __construct(array $data = []) {
        parent::__construct($data);

        $this->is_verfication_mail_sent = growfund_flash_get_message('is_verification_email_sent') ?? false;
        $this->verification_email = growfund_flash_get_message('verification_email') ?? null;
    }

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
	protected function enqueue_scripts()
    {

        $script_url = GROWFUND_DIR_URL . 'resources/assets/site/scripts/components/auth/signup-verification-mail.js';
        wp_enqueue_script('growfund-signup-verification-mail-script', $script_url, ['growfund-core'], GROWFUND_VERSION, true);
    }
}
