<?php

namespace Growfund\Constants\Pledge;

defined( 'ABSPATH' ) || exit;

use Growfund\Traits\HasConstants;

class DeliveryOption
{
    use HasConstants;

    const HOME_DELIVERY = 'home-delivery';

    const LOCAL_PICKUP = 'local-pickup';
}
