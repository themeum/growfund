<?php

namespace Growfund\Views\Components\Form;

use Growfund\View;

defined( 'ABSPATH' ) || exit;

class CheckboxField extends View {

    /** @var string */
    public $id;

    /** @var string */
    public $name;

    /** @var bool */
    public $checked = false;

    /** @var string */
    public $classname;

    /** @var string */
    public $label;

    /** @var string */
    public $label_class;

    /** @var string */
    public $style;

    /** @var string */
    public $error_msg;

    protected function get_template_dir() {
        return 'site/components/form';
    }

    protected function enqueue_styles() {
        $main_styles_url = GROWFUND_DIR_URL . 'resources/assets/site/styles/components/form/checkbox-field.css';

        wp_enqueue_style(
            'growfund-checkbox-field-styles',
            $main_styles_url,
            ['growfund-main-styles'],
            GROWFUND_VERSION
        );
    }

    protected function enqueue_scripts()
    {
        $script_url = GROWFUND_DIR_URL . 'resources/assets/site/scripts/components/form/checkbox-field.js';
        wp_enqueue_script('growfund-checkbox-field-script', $script_url, ['growfund-core'], GROWFUND_VERSION, true);
    }
}
