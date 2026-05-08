<?php

namespace Growfund\App\Listeners;

defined( 'ABSPATH' ) || exit;

use Growfund\App\Events\CampaignStatusUpdateEvent;
use Growfund\Constants\HookNames;

class CampaignPublishedListener
{
    /**
     * Event listener handler for creating activities.
     *
     * @param CampaignStatusUpdateEvent $event The event to handle.
     * 
     * @return void
     */
    public function handle($event)
    {
        $old_status = $event->campaign->status;
        $new_status = $event->status;

        if ($event->is_published($old_status, $new_status)) {
            $campaign = $event->campaign;
            $campaign->status = $new_status;
            do_action(HookNames::GROWFUND_CAMPAIGN_PUBLISHED, $campaign); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
        }
    }
}
