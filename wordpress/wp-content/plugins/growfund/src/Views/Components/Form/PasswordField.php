<?php

namespace Growfund\Views\Components\Form;

use Growfund\View;

defined( 'ABSPATH' ) || exit;

class PasswordField extends View {

    /** @var string */
    public $placeholder;

    /** @var string */
    public $classname;

    /** @var string */
    public $name; 

    /** @var string */
    public $label;

    /** @var string */
    public $id;

    /** @var string svg file path */
    public $eye_icon;

    /** @var string */
    public $error_msg;

    protected function get_template_dir() {
        return 'site/components/form';
    }

    protected function enqueue_styles() {
        $main_styles_url = GROWFUND_DIR_URL . 'resources/assets/site/styles/components/form/password-field.css';

        wp_enqueue_style(
            'growfund-password-field-styles',
            $main_styles_url,
            ['growfund-main-styles'],
            GROWFUND_VERSION
        );
    }

    protected function enqueue_scripts()
    {
        $script_url = GROWFUND_DIR_URL . 'resources/assets/site/scripts/components/form/password-field.js';
        wp_enqueue_script('growfund-password-field-script', $script_url, ['growfund-core'], GROWFUND_VERSION, true);
    }

    /**
     * Get eye icon SVG markup
     *
     * @return string
     */
    public function get_eye_icon() {
        if ( ! $this->eye_icon ) {
            return '';
        }

        return $this->get_svg_icon( $this->eye_icon );
    }
}
