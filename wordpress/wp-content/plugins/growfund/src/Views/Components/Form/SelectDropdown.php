<?php

namespace Growfund\Views\Components\Form;

use Growfund\View;

defined('ABSPATH') || exit;

class SelectDropdown extends View
{
    /**
     * @var array<array<label:string,value:mixed>> $options
     */
    public $options;
    
    /** @var string $name */
    public $name;

    /** @var mixed|null $default_value */
    public $default_value = null;

    /** @var string */
    public $placeholder;

    /** @var bool $allow_clear */
    public $allow_clear = true;

    /** @var string */
    public $classname;

    /** @var string */
    public $id;

    /** @var string */
    public $style;

    /** @var string */
    public $error_msg;

    /** @var bool */
    public $is_filterable = true;

    protected function get_template_dir() {
        return 'site/components/form';
    }

    protected function enqueue_scripts()
    {
        $script_url = GROWFUND_DIR_URL . 'resources/assets/site/scripts/components/form/select-dropdown.js';
        wp_enqueue_script('growfund-select-dropdown-script', $script_url, ['growfund-core'], GROWFUND_VERSION, true);
    }

    protected function enqueue_styles()
    {
        $main_styles_url = GROWFUND_DIR_URL . 'resources/assets/site/styles/components/form/select-dropdown.css';

        wp_enqueue_style(
                'growfund-select-dropdown-styles',
                $main_styles_url,
                ['growfund-main-styles'],
                GROWFUND_VERSION
            );
    }
}
