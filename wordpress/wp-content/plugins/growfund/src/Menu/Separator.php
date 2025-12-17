<?php

namespace Growfund\Menu;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\MenuTypes;

class Separator extends Menu
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
    protected $menu_slug = 'growfund#/';

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
        $this->page_title = __('Separator', 'growfund');
        $this->menu_title = __('<span class="growfund-menu-separator"></span>', 'growfund');

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

        add_action('admin_head', function () {
            wp_register_style('menu-separator-style', false, [], GROWFUND_VERSION);
			wp_enqueue_style('menu-separator-style');
            $css = '
                a:has(.growfund-menu-separator) {
                    width: 100%;
                    pointer-events: none;
                }

                .growfund-menu-separator {
                    display: block;
                    width: 100%;
                    height: 1px;
                    background-color: #4A5257;
                    pointer-events: none;
                }';
            wp_add_inline_style('menu-separator-style', $css);
        });
    }
}
