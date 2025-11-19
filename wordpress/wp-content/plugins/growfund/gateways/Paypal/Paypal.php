<?php

namespace Growfund\Payment\Gateways\Paypal;

defined( 'ABSPATH' ) || exit;

use Growfund\Payments\Contracts\PaymentGatewayContract;
use Growfund\Payments\Constants\WebhookType;
use Growfund\Payments\Constants\PaymentEventStatus;
use Growfund\Payments\Contracts\FuturePaymentContract;
use Growfund\Payments\DTO\{
    CustomerDTO,
    PaymentPayloadDTO,
    PaymentResponseDTO,
    RefundResponseDTO,
    PaymentStatusDTO,
    SavePaymentMethodPayloadDTO,
    WebhookResponseDTO,
};
use Exception;
use InvalidArgumentException;

class Paypal implements PaymentGatewayContract, FuturePaymentContract
{
    protected $client_id;
    protected $client_secret;
    protected $webhook_id;
    protected $base_url;
    protected $access_token;

    const TIMEOUT = 60;
    const GATEWAY_NAME = 'growfund-gateway-paypal';

    /**
     * PayPal constructor.
     *
     * Initializes the PayPal payment gateway with the provided configuration.
     *
     * @param array $config Configuration array containing client_id, client_secret, webhook_id and sandbox for PayPal integration.
     */
    public function __construct(array $config)
    {
        $this->set_config($config);
    }

    /**
     * Set configuration for PayPal gateway.
     *
     * Sets the client_id, client_secret, webhook_id and base_url of the PayPal gateway.
     * Throws an exception if the client_id and client_secret are not provided.
     * Authenticates with PayPal and sets the access_token.
     *
     * @param array $config configuration array containing client_id, client_secret, webhook_id and sandbox.
     *
     * @throws InvalidArgumentException if client_id and client_secret are not provided.
     */
    public function set_config(array $config)
    {
        $this->client_id = $config['client_id'] ?? null;
        $this->client_secret = $config['client_secret'] ?? null;
        $this->webhook_id = $config['webhook_id'] ?? null;

        if (!$this->client_id || !$this->client_secret) {
            throw new InvalidArgumentException(esc_html__('PayPal client_id and client_secret are required.', 'growfund'));
        }

        $this->base_url = $config['sandbox'] ?? true
            ? 'https://api-m.sandbox.paypal.com'
            : 'https://api-m.paypal.com';

        $this->authenticate();
    }

    /**
     * Authenticates with PayPal.
     *
     * Makes a request to the PayPal API to obtain an access token.
     * Stores the access token for future requests.
     *
     * @return void
     */
    protected function authenticate()
    {
        $auth = base64_encode($this->client_id . ':' . $this->client_secret);

        $response = wp_remote_post(
            $this->base_url . '/v1/oauth2/token',
            [
                'headers' => [
                    'Authorization' => 'Basic ' . $auth,
                    'Content-Type'  => 'application/x-www-form-urlencoded',
                ],
                'body' => [
                    'grant_type' => 'client_credentials',
                ],
                'timeout' => static::TIMEOUT,
            ]
        );

        if (is_wp_error($response)) {
            /* translators: %s: error message */
            throw new Exception(sprintf(esc_html__('PayPal authentication failed: %s', 'growfund'), esc_html($response->get_error_message())));
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (empty($data['access_token'])) {
            /* translators: %s: body */
            throw new Exception(sprintf(esc_html__('PayPal authentication failed: %s', 'growfund'), esc_html($body)));
        }

        $this->access_token = sanitize_text_field($data['access_token']);
    }


    /**
     * Create a PayPal order for immediate payment capture.
     *
     * This method sends a request to PayPal to create an order with the intent to capture
     * funds immediately. It configures the purchase unit details, including the order ID,
     * amount, currency, and description. The order also contains application context details
     * such as return and cancel URLs for redirecting the user after approval.
     *
     * @param PaymentPayloadDTO $payload Contains payment details such as order ID, amount, currency, description,
     * success URL, and cancel URL.
     * @return PaymentResponseDTO Returns a response containing redirect information for user approval and the
     * transaction ID.
     */
    public function charge(PaymentPayloadDTO $payload)
    {
        $body = [
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'invoice_id' => $payload->order_id,
                    'amount' => [
                        'currency_code' => $payload->currency,
                        'value' => $this->format_amount($payload->amount),
                    ],
                    'description' => $payload->description,
                ]
            ],
            'application_context' => [
                'return_url' => $payload->success_url,
                'cancel_url' => $payload->cancel_url,
            ]
        ];

        $response = wp_remote_post($this->base_url . '/v2/checkout/orders', [
            'headers' => $this->auth_headers(),
            'body'    => wp_json_encode($body),
            'timeout' => static::TIMEOUT,
        ]);

        if (is_wp_error($response)) {
            /* translators: %s: error message */
            throw new Exception(sprintf(esc_html__('PayPal API Error: %s', 'growfund'), esc_html($response->get_error_message())));
        }

        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $order = json_decode($body, true);

        if ($code >= 400) {
            /* translators: %s: body */
            throw new Exception(message: sprintf(esc_html__('PayPal API Error: %s', 'growfund'), esc_html($body)));
        }

        $approval_url = null;

        if (!empty($order['links'])) {
            foreach ($order['links'] as $link) {
                if ($link['rel'] === 'approve') {
                    $approval_url = $link['href'];
                    break;
                }
            }
        }

        return PaymentResponseDTO::from_array([
            'is_redirect'     => true,
            'redirect_url'    => $approval_url,
            'transaction_id'  => $order['id'],
            'payment_form'    => null,
            'raw'             => $order,
        ]);
    }


    /**
     * Create a session to save payment method using setup token
     *
     * @param SavePaymentMethodPayloadDTO $payload
     * @return PaymentResponseDTO
     */
    public function save_payment_method(SavePaymentMethodPayloadDTO $payload)
    {
        $body = [
            'payment_source' => [
                'paypal' => [
                    'permit_multiple_payment_tokens' => false,
                    'usage_pattern' => 'DEFERRED',
                    'usage_type' => 'MERCHANT',
                    'customer_type' => 'CONSUMER',
                    'experience_context' => [
                        'return_url' => $payload->redirect_url,
                        'cancel_url' => $payload->cancel_url,
                        'user_action' => 'SETUP_NOW', // 'SETUP_NOW' or 'CONTINUE'
                        'shipping_preference' => 'NO_SHIPPING',
                        'landing_page' => 'BILLING',
                    ],
                    'description' => $payload->description,
                ],
            ],
        ];

        $response = wp_remote_post($this->base_url . '/v3/vault/setup-tokens', [
            'headers' => $this->auth_headers(),
            'body'    => wp_json_encode($body),
            'timeout' => static::TIMEOUT,
        ]);

        if (is_wp_error($response)) {
            /* translators: %s: error message */
            throw new Exception(message: sprintf(esc_html__('PayPal API Error: %s', 'growfund'), esc_html($response->get_error_message())));
        }

        $code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        $setup_token = json_decode($response_body, true);

        if ($code >= 400 || empty($setup_token['id'])) {
            /* translators: %s: error message */
            throw new Exception(message: sprintf(esc_html__('PayPal vault setup token creation failed %s', 'growfund'), esc_html($response_body)));
        }

        $approval_url = null;
        $paypal_links = $setup_token['links'] ?? [];

        foreach ($paypal_links as $link) {
            if (($link['rel'] ?? '') === 'approve') {
                $approval_url = $link['href'];
                break;
            }
        }

        if (!$approval_url) {
            error_log("PayPal approval URL not found in setup token response. Full response: " . print_r($setup_token, true)); // phpcs:ignore
            throw new Exception(esc_html__('PayPal approval URL not found in setup token response.', 'growfund'));
        }

        return PaymentResponseDTO::from_array([
            'is_redirect'    => true,
            'redirect_url'   => $approval_url,
            'transaction_id' => $setup_token['id'], // temporary setup token ID
            'payment_form'   => null,
            'raw'            => $setup_token,
        ]);
    }


    /**
     * Create a customer in the PayPal system (Not needed for paypal).
     *
     * @param CustomerDTO $customer_dto Data transfer object containing customer details.
     * @return string Returns the PayPal customer ID.
     */

    public function create_customer(CustomerDTO $customer_dto)
    {
        return '';
    }

    /**
     * Charges a previously vaulted payment method (Billing Agreement). This is used for
     * subsequent charges after the initial authorization period has expired (29 days),
     * or for charging entirely new amounts using the saved payment method.
     *
     * @param PaymentPayloadDTO $payload Contains payment details such as order ID, amount, currency, description.
     * @return PaymentResponseDTO Returns a response containing the transaction ID (capture ID) and raw response.
     * @throws Exception If the charge fails.
     */
    public function charge_stored_payment_method(PaymentPayloadDTO $payload): PaymentResponseDTO
    {
        $body = [
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'invoice_id' => $payload->order_id,
                    'amount' => [
                        'currency_code' => $payload->currency,
                        'value' => $this->format_amount($payload->amount),
                    ],
                    'description' => $payload->description,
                ]
            ],
            'payment_source' => [
                'token' => [
                    'id' => $payload->payment_token_id,
                    'type' => 'PAYMENT_METHOD_TOKEN',
                ]
            ]
        ];

        $response = wp_remote_post($this->base_url . '/v2/checkout/orders', [
            'headers' => $this->auth_headers(),
            'body'    => wp_json_encode($body),
            'timeout' => static::TIMEOUT,
        ]);

        if (is_wp_error($response)) {
            throw new Exception(
                sprintf(
                    /* translators: 1: order id 2: error message */
                    esc_html__('Failed to charge vaulted payment for order %1$s: %2$s', 'growfund'),
                    esc_html($payload->order_id ?? 'N/A'),
                    esc_html($response->get_error_message())
                )
            );
        }

        $code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        $order = json_decode($response_body, true);

        if ($code >= 400) {
            throw new Exception(
                sprintf(
                    /* translators: 1: order id 2: error message */
                    esc_html__('Failed to charge vaulted payment for order %1$s: %2$s', 'growfund'),
                    esc_html($payload->order_id ?? 'N/A'),
                    esc_html($response_body)
                )
            );
        }

        $capture_info = $order['purchase_units'][0]['payments']['captures'][0] ?? null;
        $capture_id = $capture_info['id'] ?? null;
        $capture_status = $capture_info['status'] ?? 'UNKNOWN';

        if (strtolower($capture_status) !== 'completed') {
            throw new Exception(
                sprintf(
                    /* translators: $s: capture status */
                    esc_html__('Vaulted payment charge failed or incomplete. Status: %s', 'growfund'),
                    esc_html($capture_status)
                )
            );
        }

        return PaymentResponseDTO::from_array([
            'is_redirect'    => false,
            'redirect_url'   => null,
            'transaction_id' => $capture_id,
            'payment_form'   => null,
            'raw'            => $order,
        ]);
    }



    /**
     * Processes a refund for a PayPal transaction.
     *
     * This method sends a refund request to PayPal for a given transaction, optionally specifying
     * an amount and currency. If the amount is not provided, a full refund is issued. The refund
     * details are returned in the form of a RefundResponseDTO.
     *
     * @param string $transaction_id The ID of the transaction to refund.
     * @param float|null $amount The amount to refund. If not specified, the full amount is refunded.
     * @param string|null $currency The currency code for the refund amount. Defaults to 'USD' if not specified.
     * @return RefundResponseDTO Contains information about the refund status and details.
     */
    public function refund($transaction_id, $amount = null, $currency = null)
    {
        $capture_id = $transaction_id;

        $body = [];

        if ($amount) {
            $body['amount'] = [
                'value' => $this->format_amount($amount),
                'currency_code' => $currency ? strtoupper($currency) : 'USD',
            ];
        }

        $response = wp_remote_post($this->base_url . "/v2/payments/captures/{$capture_id}/refund", [
            'headers' => $this->auth_headers(),
            'body'    => wp_json_encode($body),
            'timeout' => static::TIMEOUT,
        ]);

        if (is_wp_error($response)) {
            throw new Exception(
                sprintf(
                    /* translators: %1$s - capture ID, %2$s - error message */
                    esc_html__('PayPal refund failed for capture ID %1$s: %2$s', 'growfund'),
                    esc_html($capture_id),
                    esc_html($response->get_error_message())
                )
            );
        }

        $code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        $refund_data = json_decode($response_body, true);

        if ($code >= 400 || empty($refund_data['id'])) {
            throw new Exception(
                sprintf(
                    /* translators: %1$s - capture ID, %2$s - raw API response */
                    esc_html__('PayPal refund failed for capture ID %1$s. Response: %2$s', 'growfund'),
                    esc_html($capture_id),
                    esc_html($response_body)
                )
            );
        }

        return RefundResponseDTO::from_array([
            'success'        => true,
            'refund_id'      => $refund_data['id'],
            'transaction_id' => $capture_id,
            'amount'         => $this->parse_amount($refund_data['amount']['value'] ?? 0),
            'raw'            => $refund_data,
        ]);
    }


    /**
     * Verifies the status of a PayPal transaction.
     *
     * Sends a GET request to PayPal to retrieve the status of a transaction by its ID.
     * The response is converted to a PaymentStatusDTO, which contains the status of the
     * transaction, the transaction ID, the amount, the currency, and the raw PayPal response.
     *
     * @param string $transaction_id The ID of the transaction to verify.
     * @return PaymentStatusDTO Contains information about the transaction status and details.
     */
    public function verify($transaction_id)
    {
        $response = wp_remote_get($this->base_url . "/v2/payments/captures/{$transaction_id}", [
            'headers' => $this->auth_headers(),
            'timeout' => static::TIMEOUT,
        ]);

        if (is_wp_error($response)) {
            throw new Exception(
                sprintf(
                    /* translators: %1$s - transaction ID, %2$s - error message */
                    esc_html__('Failed to verify PayPal transaction %1$s: %2$s', 'growfund'),
                    esc_html($transaction_id),
                    esc_html($response->get_error_message())
                )
            );
        }

        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $capture = json_decode($body, true);

        if ($code >= 400 || empty($capture['id'])) {
            throw new Exception(
                sprintf(
                    /* translators: %1$s - transaction ID, %2$s - raw API response */
                    esc_html__('Failed to verify PayPal transaction %1$s. Response: %2$s', 'growfund'),
                    esc_html($transaction_id),
                    esc_html($body)
                )
            );
        }

        return PaymentStatusDTO::from_array([
            'status' => strtolower($capture['status'] ?? 'unknown'),
            'transaction_id' => $transaction_id,
            'amount' => $this->parse_amount($capture['amount']['value'] ?? 0),
            'currency' => strtolower($capture['amount']['currency_code'] ?? 'usd'),
            'raw' => $capture,
        ]);
    }

    /**
     * Creates a vaulted payment method from a setup token.
     *
     * @param mixed $data The data to create the vaulted payment method.
     * @return PaymentResponseDTO|null The response DTO for the vaulted payment method.
     */
    public function confirm($data)
    {
        $setup_token = $data['approval_token_id'] ?? null;

        if (!$setup_token) {
            return null;
        }

        $body = [
            'payment_source' => [
                'token' => [
                    'id' => $setup_token,
                    'type' => 'SETUP_TOKEN',
                ]
            ]
        ];

        $response = wp_remote_post($this->base_url . '/v3/vault/payment-tokens', [
            'headers' => $this->auth_headers(),
            'body'    => wp_json_encode($body),
            'timeout' => static::TIMEOUT,
        ]);

        if (is_wp_error($response)) {
            throw new Exception(
                sprintf(
                    /* translators: %1$s - setup token ID, %2$s - error message */
                    esc_html__('Failed to confirm PayPal setup token %1$s: %2$s', 'growfund'),
                    esc_html($setup_token),
                    esc_html($response->get_error_message())
                )
            );
        }

        $code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        $result = json_decode($response_body, true);

        if ($code >= 400 || empty($result['id'])) {
            throw new Exception(
                sprintf(
                    /* translators: %1$s - setup token ID, %2$s - raw API response */
                    esc_html__('Failed to confirm PayPal setup token %1$s. Response: %2$s', 'growfund'),
                    esc_html($setup_token),
                    esc_html($response_body)
                )
            );
        }

        return PaymentResponseDTO::from_array([
            'is_redirect' => false,
            'redirect_url' => null,
            'transaction_id' => $result['id'],
            'previous_transaction_id' => $setup_token,
            'payment_form' => null,
            'raw' => $result,
        ]);
    }


    /**
     * Handle incoming webhook payloads and return standardized result.
     *
     * This method takes a webhook payload from PayPal and returns a standardized response
     * containing the type, status, transaction ID, order ID, amount, fee, currency, and raw
     * webhook payload.
     *
     * @param string $body The webhook payload from PayPal.
     * @param array $headers The HTTP headers from the webhook request.
     * @return WebhookResponseDTO A standardized response containing information about the webhook event.
     */
    public function handle_webhook($body, $headers)
    {
        $this->verify_webhook_signature($body, $headers);

        $payload = json_decode($body, true);
        $event_type = $payload['event_type'] ?? '';
        $resource = $payload['resource'] ?? [];

        $type = WebhookType::UNKNOWN;
        $status = PaymentEventStatus::PENDING;
        $transaction_id = null;
        $customer_id = null;
        $amount = 0;
        $currency = null;
        $order_id = null;
        $fee = 0;

        switch ($event_type) {
            case 'CHECKOUT.ORDER.APPROVED':
                $paypal_order_id = $resource['id'] ?? null;

                if (!$paypal_order_id) {
                    throw new Exception(esc_html__('Missing order ID in webhook payload.', 'growfund'));
                }

                $capture_info = $this->capture($paypal_order_id);

                $type = WebhookType::PAYMENT;
                $status = PaymentEventStatus::SUCCESS;
                $transaction_id = $capture_info['id'] ?? null;
                $amount = isset($capture_info['amount']) ? $this->parse_amount($capture_info['amount']['value']) : null;
                $currency = $capture_info['amount']['currency_code'] ?? null;
                $order_id = $capture_info['invoice_id'] ?? null;
                $resource = $capture_info;
                break;

            case 'PAYMENT.CAPTURE.COMPLETED':
                $type = WebhookType::PAYMENT;
                $status = PaymentEventStatus::SUCCESS;
                $transaction_id = $resource['id'] ?? null;
                $amount = isset($resource['amount']) ? $this->parse_amount($resource['amount']['value']) : null;
                $currency = $resource['amount']['currency_code'] ?? null;
                $order_id = $resource['invoice_id'] ?? null;

                if (!empty($resource['seller_receivable_breakdown']['paypal_fee']['value'])) {
                    $fee = $this->parse_amount($resource['seller_receivable_breakdown']['paypal_fee']['value']);
                }
                break;

            case 'PAYMENT.CAPTURE.REFUNDED':
                $type = WebhookType::REFUND;
                $status = PaymentEventStatus::SUCCESS;
                $transaction_id = $resource['id'] ?? null;
                $amount = isset($resource['amount']) ? $this->parse_amount($resource['amount']['value']) : null;
                $currency = $resource['amount']['currency_code'] ?? null;
                $order_id = $resource['invoice_id'] ?? null;
                break;

            case 'VAULT.PAYMENT-TOKEN.CREATED':
                $type = WebhookType::SETUP;
                $status = PaymentEventStatus::SUCCESS;
                $payment_token_id = $resource['id'] ?? null;
                $paypal_customer_id = $resource['customer']['id'] ?? null;
                $metadata = [];

                if ($payment_token_id && $paypal_customer_id) {
                    $transaction_id = $payment_token_id;
                    $customer_id = $paypal_customer_id;
                } else {
                    error_log('VAULT.PAYMENT-TOKEN.CREATED webhook missing payment_token_id or customer_id.'); // phpcs:ignore
                    $status = PaymentEventStatus::FAILED;
                }
                break;
        }

        $metadata['order_id'] = $order_id;

        return WebhookResponseDTO::from_array([
            'type' => $type,
            'status' => $status,
            'transaction_id' => $transaction_id,
            'customer_id' => $customer_id,
            'order_id' => $order_id,
            'amount' => $amount,
            'fee' => $fee,
            'currency' => strtolower($currency),
            'metadata' => $metadata,
            'payment_gateway' => static::GATEWAY_NAME,
            'raw' => $payload,
        ]);
    }

    /**
     * Formats an amount from cents to a decimal string representation.
     *
     * Converts the given amount in cents to a float, divides by 100, and formats it
     * to a string with two decimal places.
     *
     * @param int $amount The amount in cents to be formatted.
     * @return string The formatted amount as a decimal string.
     */
    public function format_amount($amount)
    {
        $amount = (float) ($amount / 100);
        return number_format($amount, 2, '.', '');
    }

    /**
     * Parse an amount from PayPal's decimal string representation to an integer value in cents.
     *
     * @param string $amount The amount in PayPal's decimal string format (e.g. '12.50').
     * @return int The parsed amount in cents (e.g. 1250).
     */
    public function parse_amount($amount)
    {
        return (int) round(floatval($amount) * 100);
    }


    /**
     * Generate the HTTP headers for authenticated requests to the PayPal API.
     *
     * Uses the stored access token to create a Bearer Authorization header.
     *
     * @return array The headers for an authenticated request.
     */
    protected function auth_headers()
    {
        return [
            'Authorization' => "Bearer {$this->access_token}",
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * Verify the signature of a webhook request from PayPal.
     *
     * Given a webhook request body and headers, make a request to PayPal's
     * `verify-webhook-signature` endpoint to verify the signature of the request.
     *
     * If the verification fails, throw an exception.
     *
     * @param string $body The webhook request body.
     * @param array $headers The webhook request headers.
     * @return bool True if the verification was successful.
     * @throws Exception If the verification failed.
     */
    protected function verify_webhook_signature($body, $headers)
    {
        $payload = [
            'auth_algo'         => $headers['Paypal-Auth-Algo'] ?? '',
            'cert_url'          => $headers['Paypal-Cert-Url'] ?? '',
            'transmission_id'   => $headers['Paypal-Transmission-Id'] ?? '',
            'transmission_sig'  => $headers['Paypal-Transmission-Sig'] ?? '',
            'transmission_time' => $headers['Paypal-Transmission-Time'] ?? '',
            'webhook_id'        => $this->webhook_id,
            'webhook_event'     => json_decode($body, true),
        ];

        $response = wp_remote_post($this->base_url . '/v1/notifications/verify-webhook-signature', [
            'headers' => $this->auth_headers(),
            'body'    => wp_json_encode($payload),
            'timeout' => static::TIMEOUT,
        ]);

        if (is_wp_error($response)) {
            throw new Exception(
                sprintf(
                    /* translators: %1$s - error message */
                    esc_html__('PayPal webhook verification request failed: %1$s', 'growfund'),
                    esc_html($response->get_error_message())
                )
            );
        }

        $code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        $result = json_decode($response_body, true);

        if ($code >= 400 || ($result['verification_status'] ?? '') !== 'SUCCESS') {
            error_log('PayPal webhook verification failed: ' . print_r($result, true)); // phpcs:ignore
            throw new Exception(esc_html__('PayPal webhook signature verification failed.', 'growfund'));
        }

        return true;
    }


    /**
     * Capture a PayPal order after approval.
     *
     * @param string $paypal_order_id The PayPal order ID to be captured.
     * @return array|null The capture info from PayPal, or null if the capture failed.
     * @throws Exception If the capture failed or was incomplete.
     */
    protected function capture($paypal_order_id)
    {
        $url = $this->base_url . "/v2/checkout/orders/{$paypal_order_id}/capture";

        $response = wp_remote_post($url, [
            'headers' => $this->auth_headers(),
            'timeout' => static::TIMEOUT,
        ]);

        if (is_wp_error($response)) {
            throw new Exception(
                sprintf(
                    /* translators: %s - error message */
                    esc_html__('PayPal auto-capture failed after approval: %s', 'growfund'),
                    esc_html($response->get_error_message())
                )
            );
        }

        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $capture_data = json_decode($body, true);

        $capture_info = $capture_data['purchase_units'][0]['payments']['captures'][0] ?? null;

        if ($code >= 400 || !$capture_info || strtolower($capture_info['status'] ?? '') !== 'completed') {
            throw new Exception(esc_html__('PayPal capture failed or incomplete.', 'growfund'));
        }

        return $capture_info;
    }
}
