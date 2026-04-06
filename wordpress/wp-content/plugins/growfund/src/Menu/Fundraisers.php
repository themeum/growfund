<?php

namespace Growfund\Menu;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\MenuTypes;

class Fundraisers extends Menu
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
    protected $menu_slug = 'growfund#/fundraisers';

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
        $this->page_title = __('Fundraisers', 'growfund');
        $this->menu_title = !growfund_app()->has_growfund_pro() 
            ? __('Fundraisers', 'growfund') . '<span class="growfund-pro-badge" style="margin-left: 5px;">' . __('Pro', 'growfund') . '</span>' 
            : __('Fundraisers', 'growfund');

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
