<?php

defined( 'ABSPATH' ) || exit;

use Growfund\Shortcodes\Auth\Login;
use Growfund\Shortcodes\Auth\Register;
use Growfund\Shortcodes\Campaigns;
use Growfund\Shortcodes\Checkout;
use Growfund\Shortcodes\Thumbnail;

return [
    Campaigns::class,
    Thumbnail::class,
    Checkout::class,
    Login::class,
    Register::class,
];
