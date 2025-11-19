<?php

defined( 'ABSPATH' ) || exit;

use Growfund\Capabilities\BackerCapabilities;
use Growfund\Capabilities\BookmarkCapabilities;
use Growfund\Capabilities\CampaignCapabilities;
use Growfund\Capabilities\DonationCapabilities;
use Growfund\Capabilities\DonorCapabilities;
use Growfund\Capabilities\FundCapabilities;
use Growfund\Capabilities\PledgeCapabilities;
use Growfund\Capabilities\TagCapabilities;

return [
    CampaignCapabilities::class,
    BackerCapabilities::class,
    BookmarkCapabilities::class,
    DonorCapabilities::class,
    DonationCapabilities::class,
    PledgeCapabilities::class,
    FundCapabilities::class,
    TagCapabilities::class,
];
