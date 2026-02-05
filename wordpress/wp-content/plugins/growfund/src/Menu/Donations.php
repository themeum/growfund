<?php

namespace Growfund\Menu;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\MenuTypes;

class Donations extends Menu
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
    protected $menu_slug = 'growfund#/donations';

    /** @inheritDoc */
    protected $parent_slug = 'growfund';

    /** @inheritDoc */
    protected $position = null;

    /**
     * The donation mode.
     * @var bool
     */
    protected $is_donation_mode;

    /**
     * The constructor of the Tools menu.
     *
     * @since 1.0.0
     */
    public function __construct($is_donation_mode)
    {
        $this->page_title = __('Donations', 'growfund');
        $this->menu_title = __('Donations', 'growfund');

        $this->is_donation_mode = $is_donation_mode;

        parent::__construct();
    }

    /** @overwrite */
    public function is_displayable()
    {
        return $this->is_donation_mode;
    }

    /** @inheritDoc */
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
