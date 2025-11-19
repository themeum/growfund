<?php

namespace Growfund\Controllers\Site;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\Status\DonationStatus;
use Growfund\Constants\Status\PaymentStatus;
use Growfund\Constants\Status\PledgeStatus;
use Growfund\Contracts\Request;
use Growfund\Payments\Constants\PaymentEventStatus;
use Growfund\Payments\Constants\WebhookType;
use Growfund\Payments\DTO\WebhookResponseDTO;
use Growfund\Services\DonationService;
use Growfund\Services\PledgeService;
use Growfund\Supports\UserMeta;
use Growfund\Supports\Utils;

class WebhookController
{
    protected $pledge_service;
    protected $donation_service;

    public function __construct()
    {
        $this->pledge_service = new PledgeService();
        $this->donation_service = new DonationService();
    }

    public function handle(Request $request)
    {
        $gateway = growfund_payment_gateway($request->get_string('gateway'));
        $body = file_get_contents('php://input');
        $headers = getallheaders();

        $webhook_response = $gateway->handle_webhook($body, $headers);

        if ($webhook_response->type === WebhookType::PAYMENT) {
            $this->handle_payment_webhook($webhook_response);
        }

        if ($webhook_response->type === WebhookType::SETUP) {
            $this->handle_setup_webhook($webhook_response);
        }

        if ($webhook_response->type === WebhookType::REFUND) {
            $this->handle_refund_webhook($webhook_response);
        }

        return [
            'status' => 'success',
        ];
    }

    public function handle_payment_webhook(WebhookResponseDTO $webhook_response)
    {
        if (Utils::is_donation_mode()) {
            $id = $webhook_response->order_id ?? null;

            if (!$id) {
                return false;
            }

            $this->donation_service->partial_update(
                $id,
                [
                    'transaction_id' => $webhook_response->transaction_id ?? null,
                    'processing_fee' => $webhook_response->fee ?? 0,
                    'status' => $webhook_response->status === PaymentEventStatus::SUCCESS
                        ? DonationStatus::COMPLETED
                        : DonationStatus::FAILED,
                    'payment_status' => PaymentEventStatus::SUCCESS
                        ? PaymentStatus::PAID
                        : PaymentStatus::FAILED
                ]
            );

            return true;
        }

        $id = $webhook_response->order_id ?? null;

        if (!$id) {
            return false;
        }

        $this->pledge_service->partial_update(
            $id,
            [
                'transaction_id' => $webhook_response->transaction_id ?? null,
                'processing_fee' => $webhook_response->fee ?? 0,
                'status' => $webhook_response->status === PaymentEventStatus::SUCCESS
                    ? PledgeStatus::BACKED
                    : PledgeStatus::FAILED,
                'payment_status' => PaymentEventStatus::SUCCESS
                    ? PaymentStatus::PAID
                    : PaymentStatus::FAILED
            ]
        );

        return true;
    }

    public function handle_setup_webhook(WebhookResponseDTO $webhook_response)
    {
        $id = $webhook_response->order_id ?? null;

        if (empty($id) && empty($webhook_response->transaction_id)) {
            return false;
        }

        if (empty($id) && !empty($webhook_response->transaction_id)) {
            $pledge = $this->pledge_service->get_by_transaction_id($webhook_response->transaction_id);

            if (!$pledge) {
                return false;
            }

            $id = $pledge->id;
        }

        $data = [
            'transaction_id' => $webhook_response->transaction_id ?? null,
            'status' => $webhook_response->status === PaymentEventStatus::SUCCESS ? PledgeStatus::PENDING : PledgeStatus::FAILED,
            'payment_status' => $webhook_response->status === PaymentEventStatus::SUCCESS ? PaymentStatus::UNPAID : PaymentStatus::PENDING
        ];

        if (!empty($webhook_response->customer_id)) {
            $pledge = $this->pledge_service->get_by_id($id);

            if ($pledge && !empty($pledge->backer->id)) {
                UserMeta::update($pledge->backer->id, $webhook_response->payment_gateway . '_customer_id', $webhook_response->customer_id);
            }
        }

        $this->pledge_service->partial_update($id, $data);

        return true;
    }

    public function handle_refund_webhook(WebhookResponseDTO $webhook_response)
    {
        $id = $webhook_response->order_id ?? null;

        if (!$id) {
            return false;
        }

        if (Utils::is_donation_mode()) {
            if ($webhook_response->status !== PaymentEventStatus::SUCCESS) {
                return false;
            }

            $this->donation_service->partial_update($id, [
                'status' => DonationStatus::REFUNDED,
                'payment_status' => PaymentStatus::REFUNDED
            ]);

            return true;
        }


        if ($webhook_response->status !== PaymentEventStatus::SUCCESS) {
            return false;
        }

        $this->pledge_service->partial_update($id, [
            'status' => PledgeStatus::REFUNDED,
            'payment_status' => PaymentStatus::REFUNDED
        ]);

        return true;
    }
}
