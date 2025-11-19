<?php

defined( 'ABSPATH' ) || exit;

use Growfund\Hooks\ActivationActions\CreateWoocommerceProduct;
use Growfund\Hooks\ActivationActions\RegisterRoles;
use Growfund\Hooks\ActivationActions\RegisterSiteRoutes;

return [
    RegisterSiteRoutes::class,
    RegisterRoles::class,
    CreateWoocommerceProduct::class
];
