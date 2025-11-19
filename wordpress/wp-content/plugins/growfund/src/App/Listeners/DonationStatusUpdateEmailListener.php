<?php

namespace Growfund\App\Listeners;

defined( 'ABSPATH' ) || exit;

use Growfund\App\Events\DonationStatusUpdateEvent;
use Growfund\Mails\Donor\DonationFailedMail;
use Growfund\Mails\Donor\DonationReceiptMail;

class DonationStatusUpdateEmailListener
{
    public function handle(DonationStatusUpdateEvent $event)
    {
        $old_status = $event->donation->status;
        $new_status = $event->status;

        if ($event->is_completed($old_status, $new_status)) {
            return $this->send_donation_receipt_mail_to_donor($event);
        }

        if ($event->is_failed($old_status, $new_status)) {
            return $this->send_donation_failed_mail_to_donor($event);
        }
    }

    protected function send_donation_failed_mail_to_donor(DonationStatusUpdateEvent $event)
    {
        growfund_scheduler()->resolve(DonationFailedMail::class)
            ->with([
                'donation_id' => $event->donation->id,
            ])
            ->group('growfund_donation_amount_charged_mails')
            ->schedule_email();
    }

    protected function send_donation_receipt_mail_to_donor(DonationStatusUpdateEvent $event)
    {
        growfund_scheduler()->resolve(DonationReceiptMail::class)
            ->with([
                'donation_id' => $event->donation->id,
            ])
            ->group('growfund_donation_amount_charged_mails')
            ->schedule_email();
    }
}
