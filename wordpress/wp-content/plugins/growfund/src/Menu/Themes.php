<?php

namespace Growfund\Menu;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\MenuTypes;

class Themes extends Menu
{
    /** @inheritDoc */
    protected $type = MenuTypes::SUB_MENU;

    /** @inheritDoc */
    protected $page_title;

    /** @inheritDoc */
    protected $menu_title;

    /** @inheritDoc */
    protected $capabilities = 'manage_options';

    /** @inheritDoc */
    protected $menu_slug = 'growfund#/themes';

    /** @inheritDoc */
    protected $position = null;

    /** @inheritDoc */
    protected $parent_slug = 'growfund';

    /**
     * The constructor of the Tools menu.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->page_title = __('Themes', 'growfund');
        $this->menu_title = __('Themes', 'growfund') 
            . '<span class="growfund-coming-soon" style="margin-left: 4px;">' . __('Coming Soon', 'growfund') . '</span>';

        parent::__construct();
    }

    public function add()
    {
        add_submenu_page(
            $this->parent_slug,
            $this->page_title,
            $this->menu_title,
            $this->capabilities,
            $this->menu_slug,
            '__return_false',
            $this->position
        );
    }
}
