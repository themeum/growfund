<?php

namespace Growfund\Hooks\ActivationActions;

defined( 'ABSPATH' ) || exit;

use Growfund\Contracts\Action;
use Growfund\Supports\Woocommerce;

class CreateWoocommerceProduct implements Action
{
    public function handle()
    {
        if (!Woocommerce::is_active()) {
            return;
        }

        Woocommerce::create_growfund_product();
    }
}
