<?php

namespace Growfund\App\Listeners;

defined( 'ABSPATH' ) || exit;

use Growfund\App\Events\GoalReachedEvent;
use Growfund\App\Events\PostUpdateEvent;
use Growfund\Services\ActivityService;

class CreateActivityListener
{
    /**
     * Event listener handler for creating activities.
     *
     * @param GoalReachedEvent|PostUpdateEvent|CampaignUpdateEvent|CampaignStatusUpdateEvent $event The event to handle.
     * 
     * @return void
     */
    public function handle($event)
    {
        $activity_dto = $event->get_activity_dto();

        if (is_null($activity_dto)) {
            return;
        }

        (new ActivityService())->save($activity_dto);
    }
}
