<?php

namespace Growfund\Hooks\Actions;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\HookNames;
use Growfund\Constants\HookTypes;
use Growfund\Hooks\BaseHook;
use Growfund\Supports\Template;

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
        return Template::get_campaign_archive_content();
    }

    public function render_campaign_details_block($attributes)
    {
        return Template::get_campaign_details_content();
    }
}
