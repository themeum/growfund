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
            growfund_redirect(site_url());
            exit;
        }

        $payment_method = growfund_payment_gateway($request->get_string('payment_method'), false);

        if (empty($payment_method)) {
            wp_die(esc_html__('Payment method not found', 'growfund'));
        }

        $gateway_data = [
            'approval_token_id' => $request->get_string('approval_token_id'), // for paypal use, @todo: need to implement generic solution later
        ];

        $response = $payment_method->confirm($gateway_data);

        if (!empty($response)) {
            if (!empty($response->previous_transaction_id) && !empty($response->transaction_id)) {
				if (growfund_app()->is_donation_mode()) {
					$donation = $this->donation_service->get_by_transaction_id($response->previous_transaction_id);
					$uid = $donation->uid;
					$this->donation_service->partial_update($donation->id, [
						'transaction_id' => $response->transaction_id ?? null
					]);
				} else {
					$pledge = $this->pledge_service->get_by_transaction_id($response->previous_transaction_id);
					$uid = $pledge->uid;
					$this->pledge_service->partial_update($pledge->id, [
						'transaction_id' => $response->transaction_id ?? null
					]);
				}
                growfund_flash_set_message('contribution_confirmed', ['uid' => $uid]);
			}

			if (!empty($response->is_redirect)) {
                $parsed_url = wp_parse_url($response->redirect_url);
				$query_args = [];

				if ( ! empty( $parsed_url['query'] ) ) {
					wp_parse_str( $parsed_url['query'], $query_args );
				}

                $uid = $query_args['uid'] ?? '';

                if (!empty($uid)) {
                    growfund_flash_set_message('contribution_confirmed', ['uid' => $uid]);
                }

                $is_failed = (bool) ($query_args['is_failed'] ?? false);

                if ($is_failed) {
                    growfund_flash_set_message('contribution_failed', ['is_failed' => true]);
                }

				growfund_redirect($response->redirect_url);
				exit;
			}
        }

        if (!empty($request->get_int('campaign_id'))) {
            if (!empty($request->has('uid'))) {
                growfund_flash_set_message('contribution_confirmed', ['uid' => $request->get_string('uid')]);
			}

			if ($request->has('failed') && $request->get_string('failed') === '1') {
				growfund_flash_set_message('contribution_failed', ['is_failed' => true]);
			}

            return growfund_redirect(growfund_campaign_url($request->get_int('campaign_id')));
        }

        return growfund_redirect(site_url());
    }
}
