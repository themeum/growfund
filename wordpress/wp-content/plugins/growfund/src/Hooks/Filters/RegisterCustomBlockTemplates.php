<?php

namespace Growfund\Hooks\Filters;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\HookNames;
use Growfund\Constants\HookTypes;
use Growfund\Hooks\BaseHook;
use Growfund\PostTypes\Campaign;
use Growfund\SiteRouter;
use WP_Block_Template;

class RegisterCustomBlockTemplates extends BaseHook
{
    public function get_name()
    {
        return HookNames::GET_BLOCK_TEMPLATES;
    }

    public function get_type()
    {
        return HookTypes::FILTER;
    }

    public function get_args_count()
    {
        return 3;
    }

    public function handle(...$args)
    {
        list($templates, $query, $template_type) = $args;

        if ($template_type !== 'wp_template' || !growfund_is_block_theme()) {
            return $templates;
        }

        $template_name = null;
        $slug = null;

        if (is_singular(Campaign::NAME)) {
            $template_name = 'campaigns/single';
            $slug = sprintf('single-%s', Campaign::NAME);
        } elseif (is_post_type_archive(Campaign::NAME)) {
            $template_name = 'campaigns/archive';
            $slug = sprintf('archive-%s', Campaign::NAME);
        } elseif (SiteRouter::is_valid_route()) {
            $template_name = growfund_is_react_site() ? 'react-page-template' : 'custom-page-template';
            $slug = 'growfund-page-' . str_replace(['.', '_'], '-', SiteRouter::get_current_route_name());
        }

        if (!$template_name) {
            return $templates;
        }

        $file_path = GROWFUND_DIR_PATH . 'resources/views/site/block-templates/' . $template_name . '.html';

        if (!file_exists($file_path)) {
            return $templates;
        }

        $content = static::get_template_content($file_path);

        $theme = wp_get_theme();

        $block                 = new WP_Block_Template();
        $block->type           = 'wp_template';
        $block->theme          = $theme->stylesheet;
        $block->slug           = $slug;
        $block->id             = 'growfund//' . $template_name;
        $block->title          = ucfirst(str_replace('-', ' ', $template_name));
        $block->source         = 'custom';
        $block->status         = 'publish';
        $block->has_theme_file = false;
        $block->is_custom      = true;
        $block->content        = $content;
        $block->post_types     = [Campaign::NAME];

        $templates[] = $block;

        return $templates;
    }

    protected static function get_template_content(string $file_path): string
    {
        if (!is_readable($file_path)) {
			growfund_error_log("Growfund: Template not readable at $file_path");

            return '';
        }

        $content = file_get_contents($file_path);

        return $content !== false ? $content : '';
    }
}
