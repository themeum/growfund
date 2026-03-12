<?php

namespace Growfund\App\Listeners;

defined( 'ABSPATH' ) || exit;

use Growfund\App\Events\SendLocalPickupInstructionEvent;
use Growfund\Mails\Backer\SendLocalPickupInstructionMail;

class SendLocalPickupInstructionEmailListener
{
    public function handle(SendLocalPickupInstructionEvent $event)
    {
        growfund_scheduler()->resolve(SendLocalPickupInstructionMail::class)
            ->with([
                'pledge_id' => $event->pledge_id,
            ])->schedule_email();
    }
}
