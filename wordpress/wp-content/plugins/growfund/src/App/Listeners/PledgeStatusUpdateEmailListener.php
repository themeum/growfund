<?php

namespace Growfund\App\Listeners;

defined( 'ABSPATH' ) || exit;

use Growfund\App\Events\PledgeStatusUpdateEvent;
use Growfund\Constants\Campaign\AppreciationType;
use Growfund\Constants\Mail\MailKeys;
use Growfund\Mails\Backer\PledgePaidMail;
use Growfund\Mails\Backer\PledgePaidWithGivingThanksMail;
use Growfund\Mails\RewardDeliveredMail;
use Growfund\Mails\PledgeCancelledMail;
use Growfund\Supports\AdminUser;
use Growfund\Supports\Date;
use Growfund\Supports\PostMeta;

class PledgeStatusUpdateEmailListener
{
    public function handle(PledgeStatusUpdateEvent $event)
    {
        $old_status = $event->pledge->status;
        $new_status = $event->status;

        if ($event->is_cancelled($old_status, $new_status)) {
            return $this->send_pledge_cancelled_mail_to_backer($event);
        }

        if ($event->is_backed($old_status, $new_status)) {
            return $this->send_backed_mail_to_backer($event);
        }

        if ($event->is_completed($old_status, $new_status) && !empty($event->pledge->reward)) {
            return $this->send_reward_delivered_mail($event);
        }
    }

    protected function send_reward_delivered_mail(PledgeStatusUpdateEvent $event)
    {
        $reward_delivered_date = Date::current_sql_safe();

        growfund_scheduler()->resolve(RewardDeliveredMail::class)
            ->with([
                'pledge_id' => $event->pledge->id,
                'receiver_user_id' => $event->pledge->backer->id,
                'reward_delivered_date' => $reward_delivered_date,
                'content_key' => MailKeys::BACKER_REWARD_DELIVERED
            ])
            ->group('growfund_reward_delivered_mails')
            ->schedule_email();

        growfund_scheduler()->resolve(RewardDeliveredMail::class)
            ->with([
                'pledge_id' => $event->pledge->id,
                'receiver_user_id' => AdminUser::get_id(),
                'reward_delivered_date' => $reward_delivered_date,
                'content_key' => MailKeys::ADMIN_REWARD_DELIVERED
            ])
            ->group('growfund_reward_delivered_mails')
            ->schedule_email();
    }


    protected function send_pledge_cancelled_mail_to_backer(PledgeStatusUpdateEvent $event)
    {
        $pledge_cancelled_date = Date::current_sql_safe();

        growfund_scheduler()->resolve(PledgeCancelledMail::class)
            ->with([
                'content_key' => MailKeys::BACKER_PLEDGE_CANCELLED,
                'pledge_id' => $event->pledge->id,
                'receiver_user_id' => $event->pledge->backer->id,
                'pledge_cancelled_date' => $pledge_cancelled_date,
            ])
            ->group('growfund_pledge_cancelled_mails')
            ->schedule_email();
    }

    protected function send_backed_mail_to_backer(PledgeStatusUpdateEvent $event)
    {
        $appreciation_type = PostMeta::get($event->pledge->campaign->id, 'appreciation_type');

        if ($appreciation_type === AppreciationType::GIVING_THANKS) {
            growfund_scheduler()->resolve(PledgePaidWithGivingThanksMail::class)
                ->with([
                    'pledge_id' => $event->pledge->id,
                ])
                ->group('growfund_pledge_amount_charged_mails')
                ->schedule_email();
        }

        growfund_scheduler()->resolve(PledgePaidMail::class)
            ->with([
                'pledge_id' => $event->pledge->id,
            ])
            ->group('growfund_pledge_amount_charged_mails')
            ->schedule_email();
    }
}
