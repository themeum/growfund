<?php

namespace Growfund\Views\Components\UI;

use Growfund\View;

defined('ABSPATH') || exit;

class Badge extends View {

    
    /** @var 'success' | 'error' | 'warning' | 'info' */
    public $variant = 'warning';

    /** @var string */
    public $message;

    /** @var string */
    public $classname;

    /** @var string svg file path */
    public $svg_icon;

    protected function get_template_dir() {
        return 'site/components/ui';
    }

    protected function enqueue_styles()
    {
        $main_styles_url = GROWFUND_DIR_URL . 'resources/assets/site/styles/components/ui/badge.css';

        wp_enqueue_style(
            'growfund-badge-styles',
            $main_styles_url,
            ['growfund-main-styles'],
            GROWFUND_VERSION
        );
    }
        /** 
     * Get SVG icon markup
     * @return string
     */
    public function get_badge_icon() {
        if ( ! $this->svg_icon ) {
            return '';
        }
        
        return $this->get_svg_icon($this->svg_icon);
    }
}
