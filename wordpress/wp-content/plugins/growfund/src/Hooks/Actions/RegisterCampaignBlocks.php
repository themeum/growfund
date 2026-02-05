<?php

namespace Growfund\Hooks\Actions;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\HookNames;
use Growfund\Constants\HookTypes;
use Growfund\Hooks\BaseHook;
use Growfund\Supports\Template;
use Growfund\View;

class RegisterCampaignBlocks extends BaseHook
{
    public function get_name()
    {
        return HookNames::INIT;
    }

    public function get_type()
    {
        return HookTypes::ACTION;
    }

    public function handle(...$args)
    {
        register_block_type('growfund/campaigns', [
            'render_callback' => [$this, 'render_campaigns_block'],
        ]);

        register_block_type('growfund/campaign-details', [
            'render_callback' => [$this, 'render_campaign_details_block'],
        ]);
    }

    public function render_campaigns_block($attributes)
    {
        return wp_kses(Template::get_campaign_archive_content(), View::get_allowed_html_tags());
    }

    public function render_campaign_details_block($attributes)
    {
        return wp_kses(Template::get_campaign_details_content(), View::get_allowed_html_tags());
    }
}
