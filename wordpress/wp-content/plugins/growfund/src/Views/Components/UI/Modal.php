<?php
namespace Growfund\Views\Components\UI;

use Growfund\View;

defined( 'ABSPATH' ) || exit;

class Modal extends View {
    /** @var string */
    public $id;

    /** @var string  */
    public $title;

    /** @var string */
    public $header_icon;

    /** @var boolean */
    public $show_footer = false;

    /** @var boolean */
    public $show_header = true;

    /** @var string Content to be rendered in the body */
    public $body_content;

    /** @var string Custom CSS class for width/height control */
    public $classname;

    /** @var string Label for the apply button */
    public $apply_label;

    /** @var string Label for the cancel button */
    public $cancel_label;

    protected function get_template_dir() {
        return 'site/components/ui';
    }

    protected function enqueue_styles() {
        wp_enqueue_style(
            'growfund-modal-styles',
            GROWFUND_DIR_URL . 'resources/assets/site/styles/components/ui/modal.css',
            ['growfund-main-styles'],
            GROWFUND_VERSION
        );
    }
    protected function enqueue_scripts()
    {
        $script_url = GROWFUND_DIR_URL . 'resources/assets/site/scripts/components/ui/modal.js';
        wp_enqueue_script('growfund-modal-script', $script_url, ['growfund-core'], GROWFUND_VERSION, true);
    }

    public function get_header_icon() {
        return $this->header_icon ? $this->get_svg_icon($this->header_icon) : '';
    }
}
