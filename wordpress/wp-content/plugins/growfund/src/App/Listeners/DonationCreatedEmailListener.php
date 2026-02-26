<?php

namespace Growfund\App\Listeners;

defined( 'ABSPATH' ) || exit;

use Growfund\App\Events\DonationCreatedEvent;
use Growfund\Constants\Mail\MailKeys;
use Growfund\Mails\Donor\OfflineDonationInstructionMail;
use Growfund\Mails\NewDonationMail;
use Growfund\Mails\NewOfflineDonationMail;
use Growfund\Supports\AdminUser;

class DonationCreatedEmailListener
{
    public function handle(DonationCreatedEvent $event)
    {
        growfund_scheduler()->resolve(
            $event->donation_create_dto->is_manual
                ? NewOfflineDonationMail::class
                : NewDonationMail::class
        )
            ->with([
                'content_key' => MailKeys::ADMIN_NEW_DONATION,
                'donation_id' => $event->donation_id,
                'receiver_user_id' => AdminUser::get_id(),
            ])
            ->group('growfund_new_donation_mails')
            ->schedule_email();

        if ($event->donation_create_dto->is_manual) {
            growfund_scheduler()->resolve(OfflineDonationInstructionMail::class)
                ->with([
                    'donation_id' => $event->donation_id,
                ])
                ->group('growfund_new_offline_donation_mails')
                ->schedule_email();
        }
        
    }
}
