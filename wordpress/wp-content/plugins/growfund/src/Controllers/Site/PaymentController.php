<?php

namespace Growfund\Controllers\Site;

defined( 'ABSPATH' ) || exit;

use Growfund\Contracts\Request;
use Growfund\Services\DonationService;
use Growfund\Services\PledgeService;

class PaymentController
{
    protected $campaign_service;
    protected $pledge_service;
    protected $reward_service;
    protected $donation_service;
    protected $donor_service;

    public function __construct()
    {
        $this->pledge_service = new PledgeService();
        $this->donation_service = new DonationService();
    }

    public function confirm(Request $request)
    {
        if (!$request->get_string('payment_method')) {
            wp_safe_redirect(site_url());
            exit;
        }

        $payment_method = growfund_payment_gateway($request->get_string('payment_method'));

        if (!$payment_method) {
            wp_die(esc_html__('Payment method not found', 'growfund'));
        }

        $response = $payment_method->confirm($request->all());

        if (!empty($response->previous_transaction_id) && !empty($response->transaction_id)) {
            if (growfund_app()->is_donation_mode()) {
                $donation = $this->donation_service->get_by_transaction_id($response->previous_transaction_id);
                $this->donation_service->partial_update($donation->id, [
                    'transaction_id' => $response->transaction_id ?? null
                ]);
            } else {
                $pledge = $this->pledge_service->get_by_transaction_id($response->previous_transaction_id);
                $this->pledge_service->partial_update($pledge->id, [
                    'transaction_id' => $response->transaction_id ?? null
                ]);
            }
        }

        if (!empty($response->is_redirect)) {
            wp_safe_redirect($response->redirect_url);
            exit;
        }

        if (!empty($request->get_int('campaign_id'))) {
            return wp_safe_redirect(growfund_campaign_url($request->get_int('campaign_id')));
        }

        return wp_safe_redirect(site_url());
    }
}
