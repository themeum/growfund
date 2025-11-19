<?php

namespace Growfund\Menu;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\MenuTypes;

class Root extends Menu
{
    /** @inheritDoc */
    protected $type = MenuTypes::MAIN_MENU;

    /** @inheritDoc */
    protected $page_title;

    /** @inheritDoc */
    protected $menu_title;

    /** @inheritDoc */
    protected $capabilities = 'read';

    /** @inheritDoc */
    protected $menu_slug = 'growfund';

    /** @inheritDoc */
    protected $icon_url = null;

    /** @inheritDoc */
    protected $position = 2;

    /**
     * The constructor of the Root menu.
     */
    public function __construct()
    {
        $svg = file_get_contents(GROWFUND_WORKING_DIRECTORY . '/resources/assets/images/logo.svg'); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
        $svg = str_replace('<svg', '<svg style="fill: none"', $svg);
        $this->icon_url = 'data:image/svg+xml;base64,' . base64_encode($svg);

        $this->page_title = __('Growfund', 'growfund');
        $this->menu_title = __('Growfund', 'growfund');

        parent::__construct();
    }

    /** @inheritDoc */
    public function add()
    {
        add_menu_page(
            $this->page_title,
            $this->menu_title,
            $this->capabilities,
            $this->menu_slug,
            [$this, 'render_page'],
            $this->icon_url,
            $this->position
        );

        remove_submenu_page($this->menu_slug, $this->menu_slug);
    }

    /**
     * Render the page.
     *
     * @return void
     * @since 1.0.0
     */
    public function render_page()
    {
        if (!growfund_user()->is_admin()) {
            growfund_redirect(growfund_user_dashboard_url());
        }

        growfund_renderer()->render('root');
    }
}
