<?php

namespace Growfund\Views\Components\Form;

use Growfund\View;

defined( 'ABSPATH' ) || exit;

class Button extends View {
    /** @var string */
    public $classname;
    
    /** @var string */
    public $id;

    /** @var string */
    public $label;

    /** @var string svg file path */
    public $svg_icon;

    /** @var string 'left' or 'right' */
    public $icon_position = 'left';

    /** @var string */
    public $style;

    /** @var string */
    public $type = 'button';

    /** @var bool */
    public $disabled = false;

    /** @var bool */
    public $has_link = false;

    /** @var string */
    public $href;

	protected function get_template_dir() {
        return 'site/components/form';
    }

    protected function enqueue_styles()
    {
        $main_styles_url = GROWFUND_DIR_URL . 'resources/assets/site/styles/components/form/button.css';

        wp_enqueue_style(
            'growfund-button-styles',
            $main_styles_url,
            ['growfund-main-styles'],
            GROWFUND_VERSION
        );
    }

    /** 
     * Get SVG icon markup
     * @return string
     */
    public function get_button_icon() {
        if ( ! $this->svg_icon ) {
            return '';
        }
        
        return $this->get_svg_icon($this->svg_icon);
    }
}
