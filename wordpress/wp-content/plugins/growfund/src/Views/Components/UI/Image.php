<?php

namespace Growfund\Views\Components\UI;

use Growfund\View;        

defined('ABSPATH') || exit;

class Image extends View
{
    /** @var string */
    public $src;

    /** @var string|null */
    public $alt;

    /** @var string|null */
    public $classname;

    /** @var string|null */
    public $style;

    /** @var string|null */
    public $acronym;

    /** @var string|null */
    public $id;

    public $object_fit = 'contain';

    protected function get_template_dir()
    {
        return 'site/components/ui';
    }

	protected function enqueue_styles()
    {
        $main_styles_url = GROWFUND_DIR_URL . 'resources/assets/site/styles/components/ui/image.css';

        wp_enqueue_style(
				'growfund-image-styles',
				$main_styles_url,
				['growfund-main-styles'],
				GROWFUND_VERSION
			);
    }
}
