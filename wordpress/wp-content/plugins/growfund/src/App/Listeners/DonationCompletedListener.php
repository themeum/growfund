<?php

namespace Growfund\App\Listeners;

defined( 'ABSPATH' ) || exit;

use Growfund\App\Events\DonationStatusUpdateEvent;
use Growfund\Constants\HookNames;

class DonationCompletedListener
{
    public function handle(DonationStatusUpdateEvent $event)
    {
        $old_status = $event->donation->status;
        $new_status = $event->status;

        if ($event->is_completed($old_status, $new_status)) {
            $donation = $event->donation;
            $donation->status = $new_status;
            do_action(HookNames::GROWFUND_DONATION_AFTER_COMPLETED_ACTION, $donation); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
        }
    }
}
