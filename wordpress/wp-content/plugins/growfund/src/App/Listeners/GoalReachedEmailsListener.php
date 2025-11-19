<?php

namespace Growfund\App\Listeners;

defined( 'ABSPATH' ) || exit;

use Growfund\App\Events\GoalReachedEvent;
use Growfund\Constants\Mail\MailKeys;
use Growfund\Mails\CampaignFundedMail;
use Growfund\Supports\AdminUser;

class GoalReachedEmailsListener
{
    public function handle(GoalReachedEvent $event)
    {
        growfund_scheduler()->resolve(CampaignFundedMail::class)
            ->with([
                'content_key' => MailKeys::ADMIN_CAMPAIGN_FUNDED,
                'campaign_id' => (int) $event->campaign->id,
                'receiver_user_id' => AdminUser::get_id(),
            ])->schedule_email();
    }
}
