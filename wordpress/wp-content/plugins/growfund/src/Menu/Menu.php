<?php

namespace Growfund\Menu;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\AppConfigKeys;
use Exception;
use Growfund\Constants\MenuTypes;
use Growfund\Supports\Option;
use Growfund\Supports\Arr;

class Menu
{
    /**
     * The menu type. Available types are:
     * - MenuTypes::MAIN_MENU
     * - MenuTypes::SUB_MENU
     *
     * @var string
     * @since 1.0.0 
     */
    protected $type = MenuTypes::MAIN_MENU;

    /**
     * The page title.
     *
     * @var string
     * @since 1.0.0
     */
    protected $menu_title = null;

    /**
     * The capabilities.
     *
     * @var string
     * @since 1.0.0
     */
    protected $capabilities = null;

    /**
     * The menu slug.
     *
     * @var string
     * @since 1.0.0
     */
    protected $menu_slug = null;

    /**
     * The icon URL.
     *
     * @var string
     * @since 1.0.0
     */
    protected $icon_url = null;

    /**
     * The position.
     *
     * @var int
     * @since 1.0.0
     */
    protected $position = null;

    /**
     * The parent slug.
     *
     * @var string
     * @since 1.0.0
     */
    protected $parent_slug = null;

    /**
     * The callback.
     *
     * @var callable
     * @since 1.0.0
     */
    protected $callback = null;

    /**
     * The page title.
     *
     * @var string
     * @since 1.0.0
     */
    protected $page_title = null;

    /**
     * The constructor.
     *
     * @param callable $callback
     * @since 1.0.0
     */
    public function __construct($callback = '')
    {
        if (! $this->check_required_properties()) {
            throw new Exception(esc_html__('Required properties are missing', 'growfund'));
        }

        $this->callback = $callback;
    }

    /**
     * Check if the menu is displayable.
     * 
     * @return bool
     */
    public function is_displayable()
    {
        return true;
    }

    /**
     * Check if the required properties are set.
     *
     * @return bool
     * @since 1.0.0
     */
    protected function check_required_properties()
    {
        $properties = [
            $this->page_title,
            $this->menu_title,
            $this->capabilities,
            $this->menu_slug,
        ];

        if (MenuTypes::SUB_MENU === $this->type) {
            $properties[] = $this->parent_slug;
        }

        $properties = Arr::make($properties);

        return $properties->every(function ($property) {
            return ! empty($property);
        });
    }

    /**
     * Add the menu.
     *
     * @return void
     * @since 1.0.0
     */
    public function add()
    {
        if ($this->type === MenuTypes::MAIN_MENU) {
            add_menu_page(
                $this->page_title,
                $this->menu_title,
                $this->capabilities,
                $this->menu_slug,
                $this->callback,
                $this->icon_url,
                $this->position
            );
        } else {
            add_submenu_page(
                $this->parent_slug,
                $this->page_title,
                $this->menu_title,
                $this->capabilities,
                $this->menu_slug,
                $this->callback,
                $this->position
            );
        }
    }

    public function __get($name)
    {
        $available_keys = [
            'menu_title',
            'capabilities',
            'menu_slug',
            'icon_url',
            'position',
            'page_title',
            'parent_slug',
        ];

        if (!in_array($name, $available_keys, true)) {
            /* translators: %s: menu name */
            throw new Exception(sprintf(esc_html__('Invalid member property %s called.', 'growfund'), esc_html($name)));
        }

        return $this->$name;
    }
}
