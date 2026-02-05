<?php

namespace Growfund\Shortcodes;

defined( 'ABSPATH' ) || exit;

use Growfund\Core\Shortcode;
use Growfund\Controllers\Site\CheckoutController;
use Growfund\Http\SiteRequest;

class Checkout extends Shortcode
{
    protected $name = 'growfund_checkout';

    public function callback($attributes, string $content = '', string $shortcode_tag = '')
    {
        $checkout_controller = new CheckoutController();
        $request = SiteRequest::instance();
        
        return $checkout_controller->show($request);
    }
}
