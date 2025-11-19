<?php

namespace Growfund\Hooks\Filters;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\HookNames;
use Growfund\Constants\HookTypes;
use Growfund\Hooks\BaseHook;
use Growfund\PostTypes\Campaign;
use Growfund\SiteRouter;

class RegisterCustomClassicTemplates extends BaseHook
{
    public function get_name()
    {
        return HookNames::TEMPLATE_INCLUDE;
    }

    public function get_type()
    {
        return HookTypes::FILTER;
    }

    public function handle(...$args)
    {
        $template = $args[0];

        if (growfund_is_block_theme() && !SiteRouter::is_dashboard_route()) {
            return $template;
        }

        if (is_post_type_archive(Campaign::NAME)) {
            return growfund_renderer()->get_path('site.campaigns.archive');
        }

        if (is_singular(Campaign::NAME)) {
            return growfund_renderer()->get_path('site.campaigns.single');
        }

        if (SiteRouter::is_valid_route()) {
            return growfund_renderer()->get_path('site.custom-page-template');
        }

        return $template;
    }
}
