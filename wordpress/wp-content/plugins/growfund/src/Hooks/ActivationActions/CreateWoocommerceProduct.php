<?php

namespace Growfund\Hooks\ActivationActions;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\OptionKeys;
use Growfund\Contracts\Action;
use Growfund\Supports\Option;
use Growfund\Supports\Woocommerce;

class CreateWoocommerceProduct implements Action
{
    public function handle()
    {
        if (!Woocommerce::is_active() && !empty(Woocommerce::get_growfund_product_id())) {
            return;
        }

        Woocommerce::create_growfund_product();
    }
}
