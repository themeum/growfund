<?php

namespace Growfund\App\Listeners;

defined( 'ABSPATH' ) || exit;

use Growfund\App\Events\PledgeStatusUpdateEvent;
use Growfund\Constants\HookNames;

class PledgeBackedListener
{
    public function handle(PledgeStatusUpdateEvent $event)
    {
        $old_status = $event->pledge->status;
        $new_status = $event->status;

        if ($event->is_backed($old_status, $new_status)) {
            $pledge = $event->pledge;
            $pledge->status = $new_status;
            do_action(HookNames::GROWFUND_PLEDGE_AFTER_BACKED_ACTION, $pledge); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
        }
    }
}
