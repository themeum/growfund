<?php

namespace Growfund\App\Listeners;

defined( 'ABSPATH' ) || exit;

use Growfund\App\Events\CampaignEndedEvent;
use Growfund\Mails\Admin\CampaignEndedMail;
use Growfund\Supports\AdminUser;

class CampaignEndedEmailListener
{
    public function handle(CampaignEndedEvent $event)
    {
        $campaign_id = $event->campaign_id;

        growfund_scheduler()->resolve(CampaignEndedMail::class)
            ->with([
                'campaign_id' => $campaign_id,
                'receiver_user_id' =>  AdminUser::get_id(),
            ])->schedule_email();
    }
}
