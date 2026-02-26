<?php

namespace Growfund\Services;

defined( 'ABSPATH' ) || exit;

use Exception;
use Growfund\Constants\PaymentEngine;
use Growfund\Constants\Status\PledgeStatus;
use Growfund\Contracts\Request;
use Growfund\Core\AppSettings;
use Growfund\DTO\Campaign\CampaignDTO;
use Growfund\DTO\Donation\CreateDonationDTO;
use Growfund\DTO\Donation\DonationDTO;
use Growfund\DTO\Donation\DonationDonorDTO;
use Growfund\DTO\Pledge\CreatePledgeDTO;
use Growfund\DTO\Pledge\PledgeBackerDTO;
use Growfund\DTO\Pledge\PledgeDTO;
use Growfund\Exceptions\ValidationException;
use Growfund\Payments\Contracts\FuturePaymentContract;
use Growfund\Payments\DTO\CustomerDTO;
use Growfund\Payments\DTO\PaymentPayloadDTO;
use Growfund\Payments\DTO\PaymentResponseDTO;
use Growfund\Payments\DTO\SavePaymentMethodPayloadDTO;
use Growfund\Sanitizer;
use Growfund\Supports\Payment;
use Growfund\Supports\User as UserSupport;
use Growfund\Supports\UserMeta;
use Growfund\Supports\Utils;
use Growfund\Supports\Woocommerce;
use Growfund\Views\Pages\DonationCheckoutPage;
use Growfund\Views\Pages\PledgeCheckoutPage;


class CheckoutService {
    /**
     * @var RewardService
     */
    private $reward_service;

    /**
     * @var PledgeService
     */
    private $pledge_service;

    /**
     * @var DonationService
     */
    private $donation_service;

    public function __construct() {
        $this->reward_service = new RewardService();
        $this->pledge_service = new PledgeService();
        $this->donation_service = new DonationService();
    }

    public function get_pledge_checkout_page(CampaignDTO $campaign, $reward_id = null) {

        $pledge_checkout = new PledgeCheckoutPage();
        $pledge_checkout->campaign = $campaign;
        $pledge_checkout->payment_methods = growfund_settings(AppSettings::PAYMENT)->get_payment_engine() === PaymentEngine::NATIVE 
            ? Payment::get_active_payment_methods()
            : [];

        if (!empty($reward_id)) {
            $reward = $this->reward_service->get_by_id($reward_id);
            $pledge_checkout->reward = $reward;
        }

        return growfund_get_html($pledge_checkout);
    }

    public function get_donation_checkout_page(CampaignDTO $campaign) {
        $donation_checkout = new DonationCheckoutPage();
        $donation_checkout->campaign = $campaign;
        $donation_checkout->payment_methods = growfund_settings(AppSettings::PAYMENT)->get_payment_engine() === PaymentEngine::NATIVE 
            ? Payment::get_active_payment_methods()
            : [];
        $donation_checkout->funds = (new FundService())->all();

        return growfund_get_html($donation_checkout);
    }

    /**
     * @param Request $request
     * @return string Redirect URL
     */
    public function create_pledge(Request $request) {
        $sanitized_data = Sanitizer::make($request, CreatePledgeDTO::checkout_sanitization_rules())->get_sanitized_data();

        $create_dto = CreatePledgeDTO::from_array($sanitized_data);
        $create_dto->user_id = growfund_user()->get_id();

        $shipping_address = $sanitized_data['shipping_address'] ?? [];
        $billing_address = $sanitized_data['billing_address'] ?? [];

        if (!empty($sanitized_data['is_billing_address_same'])) {
            $billing_address = $shipping_address;
        }

        $backer_dto = (new UserService())->get_by_user_id($create_dto->user_id);
        $backer_dto->image = $backer_dto->image['id'] ?? null;
        $user_info = PledgeBackerDTO::from_array($backer_dto->to_array());
        $user_info->shipping_address = $shipping_address;
        $user_info->is_billing_address_same = !empty($sanitized_data['is_billing_address_same']);
        $user_info->billing_address = $billing_address;

        $create_dto->user_info = wp_json_encode($user_info->to_array());
        $create_dto->status = PledgeStatus::PENDING;
        $create_dto->payment_method = !empty($sanitized_data['payment_method']) 
            ? Payment::get_payment_method_by_name($sanitized_data['payment_method']) 
            :  null;
        $create_dto->is_manual = false;

        $pledge_id = $this->pledge_service->create($create_dto);

        $pledge = $this->pledge_service->get_by_id($pledge_id);

        $response = $this->process_pledge_payment($pledge);

        growfund_flash_set_message('contribution_confirmed', ['uid' => $pledge->uid]);

        if ($response instanceof PaymentResponseDTO && $response->is_redirect) {
            return $response->redirect_url;
        }

        return growfund_campaign_url($pledge->campaign->slug);
    }

    /**
     * Process pledge payment
     * 
     * Handles pledge payment processing.
     * Creates customers, saves payment methods, and redirects to the payment gateway's payment form if necessary.
     * 
     * @param PledgeDTO $pledge 
     * @return \Growfund\Payments\DTO\PaymentResponseDTO
     * @throws Exception|ValidationException When payment gateway operations fail
     */
    protected function process_pledge_payment(PledgeDTO $pledge)
    {
        if (growfund_settings(AppSettings::PAYMENT)->get_payment_engine() === PaymentEngine::WOOCOMMERCE) {
            return $this->process_woocommerce_payment((int) $pledge->id);
        }

        $payment_method_name = $pledge->payment->payment_method->name ?? ''; 
        if (empty($payment_method_name) || Payment::is_manual_payment_method($payment_method_name)) {
            return true;
        }

        $payment_gateway = growfund_payment_gateway($payment_method_name, false);
        
        if (empty($payment_gateway)) {
            throw ValidationException::with_errors([
                'payment_method' => esc_html__('Payment gateway configuration error', 'growfund')
            ]);
        }

        if (!Payment::is_support_future_payment($payment_method_name)) {
            throw ValidationException::with_errors([
                'payment_method' => esc_html__('This payment gateway does not support future payments', 'growfund')
            ]);
        }

        $customer_id = UserMeta::get(growfund_user()->get_id(), $payment_method_name . '_customer_id');

        if (empty($customer_id)) {
            $customer_dto = new CustomerDTO([
                'user_id' => growfund_user()->get_id(),
                'name' => growfund_user()->get_display_name(),
                'email' => growfund_user()->get_email(),
            ]);

            try {
                $customer_id = $payment_gateway->create_customer($customer_dto);
                UserMeta::update(growfund_user()->get_id(), $payment_method_name . '_customer_id', $customer_id);
            } catch (Exception $error) {
                throw new Exception(esc_html__('Failed to create customer', 'growfund'));
            }
        }

        $campaign_url = growfund_campaign_url((int) $pledge->campaign->id);

        $response_dto = $payment_gateway->save_payment_method(SavePaymentMethodPayloadDTO::from_array([
            'redirect_url' => Utils::get_payment_confirm_url($payment_method_name, (int) $pledge->campaign->id, $pledge->uid),
            'success_url' => growfund_url($campaign_url, [
                'uid' => $pledge->uid
            ]),
            'cancel_url' => growfund_url($campaign_url, [
                'failed' => '1'
            ]),
            'currency' => Utils::get_currency(),
            'customer_id' => $customer_id,
            'order_id' => (int) $pledge->id,
            'description' => 'Pledge for ' . get_the_title((int) $pledge->campaign->id),
        ]));

        $is_updated = $this->pledge_service->partial_update((int) $pledge->id, [
            'transaction_id' => $response_dto->transaction_id,
        ]);

		if (!$is_updated) {
            throw new Exception(esc_html__('Failed to update pledge', 'growfund'));
        }

        return $response_dto;
    }

    /**
     * @param Request $request
     * @return string Redirect URL
     */
    public function create_donation(Request $request)
    {
        $sanitized_data = Sanitizer::make($request, CreateDonationDTO::checkout_sanitization_rules())->get_sanitized_data();

        $user_id = growfund_user()->get_id() ? growfund_user()->get_id() : null;

        $create_dto = CreateDonationDTO::from_array($sanitized_data);
        $create_dto->user_id = $user_id;
        $create_dto->is_manual = false;
        $create_dto->payment_method = !empty($sanitized_data['payment_method']) 
            ? Payment::get_payment_method_by_name($sanitized_data['payment_method']) 
            :  null;

        $is_dedicated_donation = $request->get_bool('dedicate_donation', false);

		if (!$is_dedicated_donation) {
            $create_dto->tribute_type = null;
            $create_dto->tribute_salutation = null;
            $create_dto->tribute_to = null;
            $create_dto->tribute_notification_type = null;
            $create_dto->tribute_notification_recipient_name = null;
            $create_dto->tribute_notification_recipient_phone = null;
            $create_dto->tribute_notification_recipient_email = null;
            $create_dto->tribute_notification_recipient_address = null;
		}

        $user_info = DonationDonorDTO::from_array([
            'id' => $user_id,
            'first_name' => $sanitized_data['contact_info']['first_name'] ?? growfund_user()->get_first_name(),
            'last_name' => $sanitized_data['contact_info']['last_name'] ?? growfund_user()->get_last_name(),
            'email' => $sanitized_data['contact_info']['email'] ?? growfund_user()->get_email(),
            'phone' => $user_id ? UserSupport::get_phone_number($user_id) : null,
            'username' => growfund_user()->get_username(),
            'billing_address' => $sanitized_data['billing_address'] ?? growfund_user()->get_meta('billing_address', []),
            'image' => $user_id ? UserSupport::get_avatar_image($user_id) : null,
            'joined_at' => growfund_user()->get_joined_date(),
            'is_verified' => growfund_user()->is_verified(),
            'created_by' => growfund_user()->get_created_by(),
        ]);

        $create_dto->user_info = wp_json_encode($user_info->to_array());
        $create_dto->email = $user_info->email;

        $donation_id = $this->donation_service->create($create_dto);

        $donation = $this->donation_service->get_by_id($donation_id);

        $response = $this->process_donation_payment($donation);

        growfund_flash_set_message('contribution_confirmed', ['uid' => $donation->uid]);

        if ($response instanceof PaymentResponseDTO && $response->is_redirect) {
            return $response->redirect_url;
        }

        return growfund_campaign_url($donation->campaign->slug);
    }

    /**
     * Process donation payment
     * 
     * Handles donation payment processing.
     * Creates customers, saves payment methods, and redirects to the payment gateway's payment form if necessary.
     * 
     * @param DonationDTO $donation 
     * @return \Growfund\Payments\DTO\PaymentResponseDTO
     * @throws Exception|ValidationException When payment gateway operations fail
     */
    public function process_donation_payment(DonationDTO $donation) {
        if (growfund_settings(AppSettings::PAYMENT)->get_payment_engine() === PaymentEngine::WOOCOMMERCE) {
            return $this->process_woocommerce_payment((int) $donation->id);
        }

        $payment_method_name = $donation->payment_method->name ?? ''; 

		if (empty($payment_method_name) || Payment::is_manual_payment_method($payment_method_name)) {
            return true;
        }

        $payment_gateway = growfund_payment_gateway($payment_method_name, false);
        
        if (empty($payment_gateway)) {
            throw ValidationException::with_errors([
                'payment_method' => esc_html__('Payment gateway configuration error', 'growfund')
            ]);
        }

        $campaign_url = growfund_campaign_url((int) $donation->campaign->id);

        $response_dto = $payment_gateway->charge(PaymentPayloadDTO::from_array([
            'amount' => $donation->amount,
            'description' => 'Donation to campaign: ' . $donation->campaign->title,
            'order_id' => $donation->id,
            'success_url' => growfund_url($campaign_url, [
                'uid' => $donation->uid
            ]),
            'cancel_url' => growfund_url($campaign_url, [
                'failed' => '1'
            ]),
            'currency' => Utils::get_currency(),
        ]));

        $is_updated = $this->donation_service->partial_update($donation->id, [
            'transaction_id' => $response_dto->transaction_id
        ]);

        if (!$is_updated) {
            throw new Exception(esc_html__('Failed to update donation', 'growfund'));
        }

        return $response_dto;
    }

    protected function process_woocommerce_payment(int $id) 
    {
        if (!Woocommerce::is_active()) {
            throw new Exception(esc_html__('WooCommerce plugin is not activated', 'growfund'));
        }

        $is_added = Woocommerce::set_cart_item($id);

        if ($is_added) {
            if (!Woocommerce::has_checkout_page()) {
                throw new Exception(esc_html__('Checkout is not available. Please contact with site administrator.', 'growfund'));
            }

            return growfund_redirect(wc_get_checkout_url());
        }

        throw new Exception(esc_html__('Failed to connect to WooCommerce', 'growfund'));
    }
}
