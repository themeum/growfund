<?php

namespace Growfund\Views\Components\Form;

use Growfund\View;

defined( 'ABSPATH' ) || exit;

class TextareaField extends View {
    /** @var string */
    public $placeholder;

    /** @var string */
    public $classname; 

    /** @var string */
    public $wrapper_classname; 

    /** @var string */
    public $name; 

    /** @var string */
    public $label;


    /** @var string */
    public $value; 

    /** @var string */
    public $id; 

    /** @var string */
    public $style;

    /** @var string */
    public $error_msg;


	protected function get_template_dir() {
        return 'site/components/form';
    }

    protected function enqueue_styles()
    {
        $main_styles_url = GROWFUND_DIR_URL . 'resources/assets/site/styles/components/form/textarea-field.css';

        wp_enqueue_style(
                'growfund-textarea-field-styles',
                $main_styles_url,
                ['growfund-main-styles'],
                GROWFUND_VERSION
            );
    }
}
