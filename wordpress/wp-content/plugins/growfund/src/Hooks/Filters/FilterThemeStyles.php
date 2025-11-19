<?php

namespace Growfund\Hooks\Filters;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\HookNames;
use Growfund\Constants\HookTypes;
use Growfund\Hooks\BaseHook;
use Growfund\SiteRouter;

/**
 * Filter the theme styles to prevent loading the theme styles in the user (fundraiser/donor/backer) dashboard pages.
 *
 * @since 1.0.0
 */
class FilterThemeStyles extends BaseHook
{
    public function get_name()
    {
        return HookNames::STYLE_LOADER_SRC;
    }

    public function get_type()
    {
        return HookTypes::FILTER;
    }

    public function handle(...$args)
    {
        if (empty($args)) {
            return false;
        }

        $current_route_name = SiteRouter::get_current_route_name();

        if (empty($current_route_name)) {
            return $args[0];
        }

        if (!growfund_is_dashboard_route()) {
            return $args[0];
        }

        $src = $args[0];

        if (!$src) {
            return $src;
        }

        $current_theme = wp_get_theme();
        $theme_slug = strtolower($current_theme->get_stylesheet());

        if ($this->is_theme_style($src, $theme_slug)) {
            return false;
        }

        return $src;
    }

    /**
     * Check if the style is from the theme.
     *
     * @param string $src
     * @param string $theme_slug
     *
     * @return boolean
     */
    protected function is_theme_style($src, $theme_slug)
    {
        return $src && is_string($src) && strpos($src, $theme_slug) !== false;
    }
}
