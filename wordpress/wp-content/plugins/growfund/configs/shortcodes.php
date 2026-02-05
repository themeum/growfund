<?php

defined( 'ABSPATH' ) || exit;

use Growfund\Shortcodes\Auth\Login;
use Growfund\Shortcodes\Auth\Register;
use Growfund\Shortcodes\CampaignList;
use Growfund\Shortcodes\Campaigns;
use Growfund\Shortcodes\Checkout;

return [
    Campaigns::class,
    CampaignList::class,
    Checkout::class,
    Login::class,
    Register::class,
];
