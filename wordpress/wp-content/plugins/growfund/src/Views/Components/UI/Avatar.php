<?php

namespace Growfund\Views\Components\UI;

use Growfund\View;

defined('ABSPATH') || exit;

class Avatar extends View {

    
    /** @var string */
    public $src;

    /** @var string|null */
    public $avatar_name;

    /** @var string|null */
    public $classname;

    /** @var string|null */
    public $id;

    /** @var bool */
    public $use_acronym = false;

    /** @var string */
    public $style;

    protected function get_template_dir() {
        return 'site/components/ui';
    }

    protected function enqueue_styles()
    {
        $main_styles_url = GROWFUND_DIR_URL . 'resources/assets/site/styles/components/ui/avatar.css';

        wp_enqueue_style(
                'growfund-avatar-styles',
                $main_styles_url,
                ['growfund-main-styles'],
                GROWFUND_VERSION
            );
    }
}
