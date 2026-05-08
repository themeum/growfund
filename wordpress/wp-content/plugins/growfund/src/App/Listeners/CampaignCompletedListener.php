<?php

namespace Growfund\App\Listeners;

defined( 'ABSPATH' ) || exit;

use Growfund\App\Events\CampaignUpdateEvent;
use Growfund\App\Events\CampaignStatusUpdateEvent;
use Growfund\Constants\HookNames;
use Growfund\Services\ActivityService;

class CampaignCompletedListener
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

        if ($event->is_marked_as_completed($old_status, $new_status)) {
            $campaign = $event->campaign;
            $campaign->status = $new_status;
            do_action(HookNames::GROWFUND_CAMPAIGN_COMPLETED, $campaign); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
        }
    }
}
