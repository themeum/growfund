<?php

defined( 'ABSPATH' ) || exit;

use Growfund\App\Events\CampaignEndedEvent;
use Growfund\App\Events\CampaignPostUpdateEvent;
use Growfund\App\Events\CampaignStatusUpdateEvent;
use Growfund\App\Events\CampaignUpdateEvent;
use Growfund\App\Events\DonationCreatedEvent;
use Growfund\App\Events\DonationStatusUpdateEvent;
use Growfund\App\Events\GoalReachedEvent;
use Growfund\App\Events\PledgeCreatedEvent;
use Growfund\App\Events\PledgeStatusUpdateEvent;
use Growfund\App\Events\SendLocalPickupInstructionEvent;
use Growfund\App\Listeners\CampaignCompletedListener;
use Growfund\App\Listeners\CampaignEndedEmailListener;
use Growfund\App\Listeners\CampaignPublishedListener;
use Growfund\App\Listeners\SendLocalPickupInstructionEmailListener;
use Growfund\App\Listeners\CreateActivityListener;
use Growfund\App\Listeners\DonationCompletedListener;
use Growfund\App\Listeners\DonationCreatedEmailListener;
use Growfund\App\Listeners\DonationStatusUpdateEmailListener;
use Growfund\App\Listeners\GoalReachedEmailsListener;
use Growfund\App\Listeners\PledgeBackedListener;
use Growfund\App\Listeners\PledgeCreatedEmailListener;
use Growfund\App\Listeners\PledgeStatusUpdateEmailListener;

return [
    GoalReachedEvent::class => [
        CreateActivityListener::class,
        GoalReachedEmailsListener::class,
    ],
    CampaignPostUpdateEvent::class => [
        CreateActivityListener::class,
    ],
    CampaignUpdateEvent::class => [
        CreateActivityListener::class,
    ],
    CampaignStatusUpdateEvent::class => [
        CreateActivityListener::class,
        CampaignPublishedListener::class,
        CampaignCompletedListener::class,
    ],
    CampaignEndedEvent::class => [
        CampaignEndedEmailListener::class,
    ],
    DonationCreatedEvent::class => [
        CreateActivityListener::class,
        DonationCreatedEmailListener::class,
    ],
    DonationStatusUpdateEvent::class => [
        CreateActivityListener::class,
        DonationStatusUpdateEmailListener::class,
        DonationCompletedListener::class,
    ],
    PledgeCreatedEvent::class => [
        CreateActivityListener::class,
        PledgeCreatedEmailListener::class,
    ],
    PledgeStatusUpdateEvent::class => [
        CreateActivityListener::class,
        PledgeStatusUpdateEmailListener::class,
        PledgeBackedListener::class,
    ],
    SendLocalPickupInstructionEvent::class => [
        SendLocalPickupInstructionEmailListener::class
    ],
];
