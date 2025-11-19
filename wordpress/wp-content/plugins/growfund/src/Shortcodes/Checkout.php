<?php

namespace Growfund\Shortcodes;

defined( 'ABSPATH' ) || exit;

use Growfund\Core\Shortcode;
use Growfund\Controllers\Site\CampaignCheckoutController;
use Growfund\Core\AppSettings;
use Growfund\Http\SiteRequest;

class Checkout extends Shortcode
{
    protected $name = 'growfund_checkout';

    public function callback($attributes, string $content = '', string $shortcode_tag = '')
    {
        if (growfund_settings(AppSettings::PAYMENT)->get_checkout_page_id() === null) {
            return __('Checkout page is not set in Settings->Payment', 'growfund');
        }

        $campaign_checkout_controller = new CampaignCheckoutController();

        return $campaign_checkout_controller->show(SiteRequest::instance(), true);
    }
}
