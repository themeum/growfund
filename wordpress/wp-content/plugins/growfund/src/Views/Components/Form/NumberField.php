<?php

namespace Growfund\Views\Components\Form;

use Growfund\View;

defined( 'ABSPATH' ) || exit;

class NumberField extends View {

    /** @var string */
    public $placeholder;

    /** @var string */
    public $classname;

    /** @var string svg file path */
    public $svg_icon;

    /** @var string */
    public $label;

    /** @var string */
    public $name;

    /** @var string|int|float */
    public $value;

    /** @var string */
    public $id;

    /** @var string */
    public $style;

    /** @var int|float */
    public $min;

    /** @var int|float */
    public $max;

    /** @var int|float */
    public $step;

    /** @var bool */
    public $show_currency = false;

    /** @var string */
    public $error_msg;

    protected function get_template_dir() {
        return 'site/components/form';
    }

    protected function enqueue_styles() {
        $main_styles_url = GROWFUND_DIR_URL . 'resources/assets/site/styles/components/form/number-field.css';

        wp_enqueue_style(
            'growfund-number-field-styles',
            $main_styles_url,
            ['growfund-main-styles'],
            GROWFUND_VERSION
        );
    }

    protected function enqueue_scripts()
    {
        $script_url = GROWFUND_DIR_URL . 'resources/assets/site/scripts/components/form/number-field.js';
        wp_enqueue_script('growfund-number-field-script', $script_url, ['growfund-core'], GROWFUND_VERSION, true);
    }

    /**
     * Get SVG icon markup
     *
     * @return string
     */
    public function get_number_field_icon() {
        if ( ! $this->svg_icon ) {
            return '';
        }

        return $this->get_svg_icon( $this->svg_icon );
    }
}
