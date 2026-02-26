<?php

namespace Growfund\Views\Components\Form;

use Growfund\View;

defined( 'ABSPATH' ) || exit;

class RadioField extends View {
    /** @var string */
    public $id;

    /** @var string */
    public $name;

    /** @var string */
    public $value;

    /** @var bool */
    public $checked = false;

    /** @var string */
    public $classname;

    /** @var string */
    public $wrapper_class;

    /** @var string */
    public $label;

    /** @var string */
    public $title;

    /** @var string */
    public $style;

    public $icon;

    /** @var bool */
    public $disabled = false;


    protected function get_template_dir() {
        return 'site/components/form';
    }

    protected function enqueue_styles()
    {
        $main_styles_url = GROWFUND_DIR_URL . 'resources/assets/site/styles/components/form/radio-field.css';

        wp_enqueue_style(
            'growfund-radio-field-styles',
            $main_styles_url,
            ['growfund-main-styles'],
            GROWFUND_VERSION
        );
    }
}
