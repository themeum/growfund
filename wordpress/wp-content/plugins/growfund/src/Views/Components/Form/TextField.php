<?php

namespace Growfund\Views\Components\Form;

use Growfund\View;

defined( 'ABSPATH' ) || exit;

class TextField extends View {
    /** @var string */
    public $placeholder;

    /** @var string */
    public $classname; 

    /** @var string svg file path */
    public $svg_icon;

    /** @var bool $allow_clear */
    public $allow_clear = false;

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
    public $wrapper_style;

    /** @var string */
    public $error_msg;


	protected function get_template_dir() {
        return 'site/components/form';
    }

    protected function enqueue_styles()
    {
        $main_styles_url = GROWFUND_DIR_URL . 'resources/assets/site/styles/components/form/text-field.css';

        wp_enqueue_style(
                'growfund-text-field-styles',
                $main_styles_url,
                ['growfund-main-styles'],
                GROWFUND_VERSION
            );
    }

	protected function enqueue_scripts()
    {
        $script_url = GROWFUND_DIR_URL . 'resources/assets/site/scripts/components/form/text-field.js';
        wp_enqueue_script('growfund-text-field-script', $script_url, ['growfund-core'], GROWFUND_VERSION, true);
    }

    /** 
     * Get SVG icon markup
     * @return string
     */
    public function get_text_field_icon() {
        if ( ! $this->svg_icon ) {
            return '';
        }
        
        return $this->get_svg_icon($this->svg_icon);
    }
}
