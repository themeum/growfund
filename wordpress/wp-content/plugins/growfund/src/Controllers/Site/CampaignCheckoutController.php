<?php

namespace Growfund\Controllers\Site;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\PaymentEngine;
use Growfund\Constants\Status\DonationStatus;
use Growfund\Constants\Status\PaymentStatus;
use Growfund\Constants\Status\PledgeStatus;
use Growfund\Contracts\Request;
use Growfund\Core\AppSettings;
use Growfund\DTO\Donation\CreateDonationDTO;
use Growfund\DTO\Pledge\CreatePledgeDTO;
use Growfund\DTO\Site\CheckoutDTO;
use Growfund\DTO\Site\OrderSummaryDTO;
use Growfund\DTO\Woocommerce\WoocommerceContributionDTO;
use Growfund\Exceptions\NotFoundException;
use Growfund\Exceptions\ValidationException;
use Growfund\Payments\Contracts\FuturePaymentContract;
use Growfund\Payments\DTO\CustomerDTO;
use Growfund\Payments\DTO\PaymentPayloadDTO;
use Growfund\Payments\DTO\SavePaymentMethodPayloadDTO;
use Growfund\Sanitizer;
use Growfund\Services\DonationService;
use Growfund\Services\DonorService;
use Growfund\Services\PledgeService;
use Growfund\Services\RewardService;
use Growfund\Services\CampaignService;
use Growfund\PostTypes\Campaign;
use Growfund\Supports\Utils;
use Growfund\Supports\UserMeta;
use Growfund\Validation\Validator;
use Growfund\Http\Response;
use Growfund\Payments\Constants\PaymentGatewayType;
use Growfund\Supports\Woocommerce;
use Exception;
use Growfund\Constants\Campaign\ReachingAction;
use Growfund\Constants\Status\CampaignStatus;
use Growfund\DTO\Donor\DonorDTO;
use InvalidArgumentException;
use Growfund\Services\Site\RewardService as SiteRewardService;
use Growfund\Supports\CampaignGoal;
use Growfund\Supports\Money;
use Growfund\Supports\Payment;
use Growfund\Supports\Fund;
use Growfund\Supports\PriceCalculator;
use Growfund\Supports\Url;

/**
 * Campaign Checkout Controller
 * 
 * Handles the checkout process for both donation and pledge modes.
 * Manages payment processing, validation, and user interactions during
 * campaign contribution checkout flows.
 * 
 * @package Growfund\Controllers\Site
 * @since 1.0.0
 */
class CampaignCheckoutController
{
    /** @var CampaignService */
    protected $campaign_service;

    /** @var PledgeService */
    protected $pledge_service;

    /** @var RewardService */
    protected $reward_service;

    /** @var DonationService */
    protected $donation_service;

    /** @var DonorService */
    protected $donor_service;

    /**
     * Initialize controller with required services
     */
    public function __construct()
    {
        $this->campaign_service = new CampaignService();
        $this->pledge_service = new PledgeService();
        $this->reward_service = new RewardService();
        $this->donation_service = new DonationService();
        $this->donor_service = new DonorService();
    }

    /**
     * Shows the checkout page for a campaign.
     * 
     * Depending on the payment engine, this function will either redirect to the
     * WooCommerce checkout page or render the Growfund checkout template.
     * 
     * @since 1.0.0
     * 
     * @param Request $request
     * 
     * @return mixed
     * 
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ValidationException
     */
    public function show(Request $request, bool $is_shortcode = false)
    {
        $id = $request->get_int('campaign_id');

		$campaign_dto = $this->get_valid_campaign_dto($id);

        $campaign_dto->get_values();

        if (growfund_payment_engine() === PaymentEngine::WOOCOMMERCE) {
            return $this->apply_wc_checkout($id, $request->get_int('reward_id'), $request->get_float('amount'));
        }

        if (growfund_app()->is_donation_mode()) {
            return $this->donation_checkout_page($campaign_dto, $is_shortcode);
        }

        return $this->pledge_checkout_page($campaign_dto, $request->get_int('reward_id'), $request->get_float('amount'), $is_shortcode);
    }

    /**
     * Process checkout form submission
     * 
     * Handles both donation and pledge checkout submissions.
     * Validates form data, processes payments, and creates appropriate records.
     * 
     * @param Request $request The incoming request object
     * @return mixed Redirect response or processed result
     * @throws Exception When security check fails or payment method not found
     */
    public function store(Request $request)
    {
        $id = $request->get_int('campaign_id');

        $this->get_valid_campaign_dto($id);

        if (empty($request->get_string('payment_method'))) {
            return $this->handle_validation_error([
                'payment_method' => __('Please select a payment method.', 'growfund')
            ], null, ['campaign_id' => $id]);
        }

        if (!Payment::is_valid_payment_method($request->get_string('payment_method'))) {
            return $this->handle_validation_error([
                'payment_method' => __('Payment method not found', 'growfund')
            ], null, ['campaign_id' => $id]);
        }

        $data = array_merge($request->all(), ['campaign_id' => $id]);

        if (growfund_app()->is_donation_mode()) {
            return $this->process_donation($data);
        }

        return $this->process_pledge($data);
    }

    /**
     * Calculate shipping cost via AJAX request
     * 
     * Determines shipping cost based on reward and destination country.
     * Returns formatted shipping cost information for frontend display.
     * 
     * @param Request $request The incoming request object
     * @return \Growfund\Http\Response JSON response with shipping cost details
     * @throws Exception When reward is not found
     */
    public function calculate_shipping_cost(Request $request)
    {
        $reward_id = $request->get_int('reward_id');
        $country = $request->get_string('country');

        $site_reward_service = new SiteRewardService();
        $reward_dto = $site_reward_service->get_by_id($reward_id);

        if (!$reward_dto) {
            throw new Exception(esc_html__('Reward not found', 'growfund'), (int) Response::NOT_FOUND);
        }

        $shipping_address = [
            'country' => $country
        ];

        $shipping_cost = PriceCalculator::calculate_shipping_cost($shipping_address, $reward_dto);

        return growfund_site_response()->json([
            'shipping_cost' => $shipping_cost,
            'formatted_cost' => number_format($shipping_cost, 2),
            'currency' => Utils::get_currency()
        ]);
    }

    public function ajax_update_wc_session(Request $request)
    {
        $contribution_info_dto = Woocommerce::get_custom_cart_info_from_session();

        if (!empty($contribution_info_dto) && !empty($request->get_float('bonus_support_amount'))) {
            $contribution_info_dto->bonus_support_amount = $request->get_float('bonus_support_amount');
            Woocommerce::set_custom_cart_info_to_session($contribution_info_dto);
        }

        if (!empty($contribution_info_dto) && !empty($request->get_float('contribution_amount'))) {
            $contribution_info_dto->contribution_amount = $request->get_float('contribution_amount');
            Woocommerce::set_custom_cart_info_to_session($contribution_info_dto);
        }

        return growfund_site_response()->json([]);
    }

    protected function get_valid_campaign_dto($campaign_id) {
        if (empty($campaign_id)) {
            throw new NotFoundException(esc_html__('Campaign not specified', 'growfund'), (int) Response::NOT_FOUND);
        }

        $campaign_dto = $this->campaign_service->get_by_id($campaign_id);

		if (CampaignGoal::is_reached($campaign_dto) && $campaign_dto->reaching_action === ReachingAction::CLOSE) {
            throw new NotFoundException(esc_html__('Campaign is closed', 'growfund'), (int) Response::NOT_FOUND);
        }

        if (($campaign_dto->status !== CampaignStatus::PUBLISHED && $campaign_dto->status !== CampaignStatus::FUNDED) || $campaign_dto->is_hidden) {
            throw new NotFoundException(esc_html__('Campaign not found', 'growfund'), (int) Response::NOT_FOUND);
        }
        
        if (!$campaign_dto) {
            throw new NotFoundException(esc_html__('Campaign not found', 'growfund'), (int) Response::NOT_FOUND);
        }

        return $campaign_dto;
    }

    /**
     * Returns the donation checkout page for a campaign.
     *
     * @param int $campaign_id
     * @param bool $is_shortcode
     *
     * @return string
     */
    protected function donation_checkout_page($campaign_dto, $is_shortcode = false)
    {
        if (growfund_app()->is_donation_mode() && !growfund_user()->is_logged_in() && !growfund_settings(AppSettings::PAYMENT)->allow_guest_checkout()) {
            return growfund_redirect(growfund_login_url(growfund_campaign_url($campaign_dto->id)));
        }

        $campaign_image = '';

        if (!empty($campaign_dto->images) && is_array($campaign_dto->images)) {
            $campaign_image = $campaign_dto->images[0]['url'] ?? '';
        }

        $funds = Fund::get_funds_for_donation($campaign_dto->id);
        $suggested_options = $campaign_dto->suggested_options;
        $is_user_logged_in = growfund_user()->is_logged_in();
        $current_user = $is_user_logged_in ? growfund_user()->get() : null;

        $is_tribute_enabled = growfund_settings(AppSettings::CAMPAIGNS)->allow_tribute() && $campaign_dto->has_tribute;

        $consent_text = growfund_settings(AppSettings::GENERAL)->get_tnc_text();
        $consent_text = $consent_text ? $consent_text : __('I have read and agree to the terms and conditions above.', 'growfund');

        return growfund_renderer()->get_html('site.donation_checkout', [
            'campaign_id' => $campaign_dto->id,
            'campaign_title' => $campaign_dto->title,
            'campaign_image' => $campaign_image,
            'min_donation_amount' => $campaign_dto->min_donation_amount,
            'max_donation_amount' => $campaign_dto->max_donation_amount,
            'allow_custom_donation' => $campaign_dto->allow_custom_donation,
            'suggested_options' => $suggested_options,
            'tribute_requirement' => $campaign_dto->tribute_requirement,
            'tribute_title' => $campaign_dto->tribute_title,
            'tribute_options' => $campaign_dto->tribute_options,
            'tribute_notification_preference' => $campaign_dto->tribute_notification_preference,
            'is_tribute_enabled' => $is_tribute_enabled,
            'fund_selection_type' => $campaign_dto->fund_selection_type ?? null,
            'default_fund' => $campaign_dto->default_fund ?? null,
            'funds' => $funds,
            'is_user_logged_in' => $is_user_logged_in,
            'current_user' => $current_user,
            'payment_methods' => $this->get_payment_method_options(),
            'allow_anonymous_donation' => growfund_settings(AppSettings::PERMISSIONS)->allow_anonymous_donation(),
            'is_shortcode' => $is_shortcode,
            'consent_text' => $consent_text,
        ]);
    }

    /**
     * Displays the checkout page for a campaign, handling non-logged-in and logged-in users differently.
     * When the user is logged in, it fetches the campaign data and renders the checkout template.
     * When the user is not logged in, it redirects to the login page with a validation error.
     * @param int $campaign_id The campaign ID
     * @param int $reward_id The reward ID
     * @param bool $is_shortcode
     * @return string The rendered HTML
     */
    protected function pledge_checkout_page($campaign_dto, $reward_id = null, $amount = 0, $is_shortcode = false)
    {
        if (!growfund_user()->is_logged_in()) {
            return $this->handle_validation_error([
                'user_id' => __('You need to be logged in to make a pledge', 'growfund')
            ], growfund_campaign_url($campaign_dto->id), ['campaign_id' => $campaign_dto->id]);
        }

        $reward_data = null;
        $order_summary = new OrderSummaryDTO([
            'bonus_support' => 0,
            'shipping' => 0,
            'total' => 0
        ]);

        if (!empty($reward_id)) {
            $site_reward_service = new SiteRewardService();
            $checkout_reward = $site_reward_service->get_checkout_reward_data($reward_id);
            $reward_data = $checkout_reward['reward_data'];
            $order_summary = $checkout_reward['order_summary'];
        }

        $consent_text = growfund_settings(AppSettings::GENERAL)->get_tnc_text();
        $consent_text = $consent_text ? $consent_text : __('I have read and agree to the terms and conditions above.', 'growfund');

        $user_shipping_address = null;

        if (growfund_user()->is_logged_in()) {
            $raw_shipping_address = UserMeta::get(growfund_user()->get_id(), 'shipping_address');
            $user_shipping_address = !empty($raw_shipping_address)
                ? maybe_unserialize($raw_shipping_address)
                : null;
        }

        $campaign_slug = null;
        $campaign_post = get_post($campaign_dto->id);

        if ($campaign_post && $campaign_post->post_type === Campaign::NAME) {
            $campaign_slug = $campaign_post->post_name;
        }

        $campaign_data = $this->campaign_service->get_by_id($campaign_dto->id);

        $checkout_dto = CheckoutDTO::from_array([
            'campaign_id' => $campaign_dto->id,
            'reward_id' => $reward_id,
            'reward_data' => $reward_data,
            'order_summary' => $order_summary,
            'consent_text' => $consent_text,
            'user_shipping_address' => $user_shipping_address,
            'campaign_slug' => $campaign_slug,
        ]);

        return growfund_renderer()->get_html('site.checkout', [
            'checkout' => $checkout_dto,
            'payment_methods' => $this->get_payment_method_options(),
            'campaign_data' => $campaign_data,
            'is_shortcode' => $is_shortcode,
            'amount' => $amount
        ]);
    }

    protected function get_payment_method_options()
    {
        $payment_methods = Payment::get_active_payment_methods();
        $options = [];

        foreach ($payment_methods as $key => $payment_method) {
            if (!growfund_app()->is_donation_mode() && $payment_method->type === PaymentGatewayType::ONLINE && !$payment_method->supports_future_payments) {
                continue;
            }

            $options[] = [
                'value' => $payment_method->name,
                'label' => $payment_method->config['label'],
                'icon' => $payment_method->config['logo'],
            ];
        }

        return $options;
    }

    /**
     * Process pledge checkout submission
     * 
     * Handles pledge creation, payment method setup, and shipping address storage.
     * Creates customer records and initiates payment method saving process.
     * 
     * @param array $data The validated pledge data
     * @return mixed Redirect response or validation error
     * @throws Exception When payment gateway operations fail
     */
    protected function process_pledge(array $data)
    {
        if (!growfund_user()->is_logged_in()) {
            return growfund_redirect(growfund_login_url(growfund_campaign_url($data['campaign_id'])));
        }

        $validator = Validator::make($data, CreatePledgeDTO::checkout_validation_rules());

        if ($validator->is_failed()) {
            return $this->handle_validation_error($validator->get_errors(), null, $data);
        }

        if (!empty($data['reward_id'])) {
            $shipping_address = [
                'address' => $data['address'],
                'address_2' => $data['address_2'] ?? '',
                'city' => $data['city'],
                'state' => $data['state'],
                'zip_code' => $data['zip_code'],
                'country' => $data['country']
            ];

            UserMeta::update(growfund_user()->get_id(), 'shipping_address', $shipping_address);
            clean_user_cache(growfund_user()->get_id());
        }

        $data = Sanitizer::make($data, CreatePledgeDTO::sanitization_rules())->get_sanitized_data();

        $data['payment_method'] = Payment::get_payment_method_by_name($data['payment_method']);

        if (empty($data['payment_method'])) {
            throw new Exception(esc_html__('Payment method not found', 'growfund'), (int) Response::NOT_FOUND);
        }

        $data['user_id'] = growfund_user()->get_id();
        $data['status'] = PledgeStatus::PENDING;
        $data['payment_engine'] = growfund_payment_engine();

        try {
            $pledge_id = $this->pledge_service->create(CreatePledgeDTO::from_array($data));
            $this->process_pledge_payment($data['payment_method']->name, $pledge_id, $data['campaign_id']);

            $pledge = $this->pledge_service->get_by_id($pledge_id);
            $redirect_url = growfund_url(growfund_campaign_url($data['campaign_id']), [
                'uid' => $pledge->uid
            ]);

            return growfund_redirect($redirect_url);
        } catch (ValidationException $error) {
            return $this->handle_validation_error($error->get_errors(), null, $data);
        } catch (Exception $error) {
            return $this->handle_validation_error([
                'general' => $error->getMessage()
            ], null, $data);
        }
    }

    /**
     * Process donation checkout submission
     * 
     * Handles donation creation, donor management, and payment processing.
     * Creates anonymous donors for non-logged-in users and processes payments.
     * 
     * @param array $data The validated donation data
     * @return mixed Redirect response or validation error
     * @throws Exception When payment gateway operations fail
     */
    protected function process_donation(array $data)
    {
        if (growfund_app()->is_donation_mode() && !growfund_user()->is_logged_in() && !growfund_settings(AppSettings::PAYMENT)->allow_guest_checkout()) {
            return growfund_redirect(growfund_login_url(growfund_campaign_url($data['campaign_id'])));
        }

        try {
            $data = $this->validate_and_prepare_donation_data($data);
            $user_data = [
				'first_name' => $data['donor_first_name'],
				'last_name' => $data['donor_last_name'],
				'email' => $data['donor_email'],
				'phone' => $data['donor_phone']
            ];
            $user_id =  !growfund_user()->is_logged_in() ? null : growfund_user()->get_id();
            $data = Sanitizer::make($data, CreateDonationDTO::sanitization_rules())->get_sanitized_data();

            $create_dto = new CreateDonationDTO();
            $create_dto->user_id = $user_id;
            $create_dto->campaign_id = $data['campaign_id'];
            $create_dto->fund_id = $data['fund_id'] ?? null;
            $create_dto->status = DonationStatus::PENDING;
            $create_dto->amount = $data['amount'];

            if (empty($user_id)) {
                $user_info = DonorDTO::from_array($user_data);
				$create_dto->user_info = wp_json_encode($user_info->to_array());
				$create_dto->email = $user_info->email;
            }

            if (!empty($data['dedicate_donation']) && $data['dedicate_donation'] === 'on') {
                $create_dto->tribute_type = $data['tribute_type'] ?? null;
                $create_dto->tribute_salutation = $data['tribute_salutation'] ?? null;
                $create_dto->tribute_to = $data['tribute_to'] ?? null;
                $create_dto->tribute_notification_type = $data['tribute_notification_type'] ?? null;
                $create_dto->tribute_notification_recipient_name = $data['tribute_notification_recipient_name'] ?? null;
                $create_dto->tribute_notification_recipient_phone = $data['tribute_notification_recipient_phone'] ?? null;
                $create_dto->tribute_notification_recipient_email = $data['tribute_notification_recipient_email'] ?? null;
                $create_dto->tribute_notification_recipient_address = $data['tribute_notification_recipient_address'] ?? null;
            }

            $create_dto->notes = !empty($data['notes']) ? $data['notes'] : null;
            $create_dto->transaction_id = null;
            $create_dto->payment_engine = growfund_payment_engine();
            $create_dto->payment_method = Payment::get_payment_method_by_name($data['payment_method']);
            $create_dto->payment_status = PaymentStatus::UNPAID;
            $create_dto->is_anonymous = growfund_settings(AppSettings::PERMISSIONS)->allow_anonymous_donation() && !empty($data['is_anonymous']) ? $data['is_anonymous'] : false;

            if (empty($create_dto->payment_method)) {
                throw new Exception(__('Payment method not found', 'growfund'));
            }

            $donation_id = $this->donation_service->create($create_dto);

            $this->process_donation_payment($data['amount'], $data['payment_method'], $donation_id, $data['campaign_id']);

            $donation = $this->donation_service->get_by_id($donation_id);

            $redirect_url = growfund_url(growfund_campaign_url($data['campaign_id']), [
                'uid' => $donation->uid
            ]);

            return growfund_redirect($redirect_url);
        } catch (Exception $error) {
            return $this->handle_validation_error([
                'general' => $error->getMessage()
            ], null, $data);
        }
    }

    /**
     * Process pledge payment
     * 
     * Handles pledge payment processing.
     * Creates customers, saves payment methods, and redirects to the payment gateway's payment form if necessary.
     * 
     * @param string $payment_method The payment method
     * @param int $pledge_id The pledge ID
     * @param int $campaign_id The campaign ID
     * @return mixed Redirect response or true if payment was successful
     * @throws Exception When payment gateway operations fail
     */
    protected function process_pledge_payment($payment_method, $pledge_id, $campaign_id)
    {
        if (Payment::is_manual_payment_method($payment_method)) {
            return true;
        }

        try {
            $payment_gateway = growfund_payment_gateway($payment_method);
        } catch (Exception $error) {
            return $this->handle_validation_error([
                /* translators: %s: error message */
                'payment_method' => sprintf(__('Payment gateway configuration error: %s', 'growfund'), $error->getMessage())
            ]);
        }

        if (!$payment_gateway instanceof FuturePaymentContract) {
            return $this->handle_validation_error([
                'payment_method' => __('This payment gateway does not support future payments', 'growfund')
            ]);
        }

        $customer_id = UserMeta::get(growfund_user()->get_id(), $payment_method . '_customer_id');

        if (empty($customer_id)) {
            $customer_dto = new CustomerDTO([
                'user_id' => growfund_user()->get_id(),
                'name' => growfund_user()->get_display_name(),
                'email' => growfund_user()->get_email(),
            ]);

            try {
                $customer_id = $payment_gateway->create_customer($customer_dto);
                UserMeta::update(growfund_user()->get_id(), $payment_method . '_customer_id', $customer_id);
            } catch (Exception $error) {
                throw new Exception(esc_html__('Failed to create customer', 'growfund'));
            }
        }

        $pledge = $this->pledge_service->get_by_id($pledge_id);

        $response_dto = $payment_gateway->save_payment_method(SavePaymentMethodPayloadDTO::from_array([
            'redirect_url' => Utils::get_payment_confirm_url($payment_method, $campaign_id),
            'success_url' => growfund_url(growfund_campaign_url($campaign_id), [
                'uid' => $pledge->uid
            ]),
            'cancel_url' => growfund_url(growfund_campaign_url($campaign_id), [
                'failed' => '1'
            ]),
            'currency' => Utils::get_currency(),
            'customer_id' => $customer_id,
            'order_id' => $pledge_id,
            'description' => 'Pledge for ' . get_the_title($campaign_id),
        ]));

        $this->pledge_service->partial_update($pledge_id, [
            'transaction_id' => $response_dto->transaction_id,
        ]);

        if ($response_dto->is_redirect) {
            return growfund_redirect($response_dto->redirect_url);
        }

        return true;
    }

    /**
     * Process donation payment
     * 
     * Handles donation payment processing.
     * Redirects to the payment gateway's payment form if necessary.
     * 
     * @param float $amount The donation amount
     * @param string $payment_method The payment method
     * @param int $donation_id The donation ID
     * @param int $campaign_id The campaign ID
     * @return mixed Redirect response or true if payment was successful
     * @throws Exception When payment gateway operations fail
     */
    protected function process_donation_payment($amount, $payment_method, $donation_id, $campaign_id)
    {
        if (Payment::is_manual_payment_method($payment_method)) {
            return true;
        }

        try {
            $payment_gateway = growfund_payment_gateway($payment_method);
        } catch (Exception $error) {
            return $this->handle_validation_error([
                /* translators: %s: error message */
                'payment_method' => sprintf(__('Payment gateway configuration error: %s', 'growfund'), $error->getMessage())
            ]);
        }

        $donation = $this->donation_service->get_by_id($donation_id);

        $response_dto = $payment_gateway->charge(PaymentPayloadDTO::from_array([
            'amount' => $amount,
            'description' => 'Donation to campaign: ' . get_the_title($campaign_id),
            'order_id' => $donation_id,
            'success_url' => growfund_url(growfund_campaign_url($campaign_id), [
                'uid' => $donation->uid
            ]),
            'cancel_url' => growfund_url(growfund_campaign_url($campaign_id), [
                'failed' => '1'
            ]),
            'currency' => Utils::get_currency(),
        ]));

        $this->donation_service->partial_update($donation_id, [
            'transaction_id' => $response_dto->transaction_id
        ]);

        if ($response_dto->is_redirect) {
            return growfund_redirect($response_dto->redirect_url);
        }

        return true;
    }

    /**
     * Handle validation errors during checkout process
     * 
     * Sets flash messages for validation errors and redirects to appropriate
     * location based on available redirect URLs or referrer.
     * 
     * @param array $errors Array of validation error messages
     * @param string|null $redirect_url Optional specific redirect URL
     * @param array $data Additional data for fallback redirects
     * @return mixed Redirect response
     */
    protected function handle_validation_error(array $errors, $redirect_url = null, array $data = [])
    {
        if (!empty($errors)) {
            growfund_flash_set_message('validation_errors', $errors);
        }

        if (!empty($redirect_url)) {
            return growfund_redirect($redirect_url);
        }

        $referer = wp_get_referer();

        if ($referer) {
            return growfund_redirect($referer);
        }

        return growfund_redirect(growfund_campaign_url($data['campaign_id'] ?? home_url()));
    }

    /**
     * Validate and prepare donation data for processing
     * 
     * Performs comprehensive validation of donation data including
     * campaign existence, tribute requirements, and donor information.
     * Prepares data with campaign-specific requirements and user metadata.
     * 
     * @param array $data The raw donation data
     * @return array The validated and prepared donation data
     * @throws Exception When campaign is not found
     */
    protected function validate_and_prepare_donation_data(array $data)
    {
        $campaign_dto = $this->campaign_service->get_by_id($data['campaign_id']);

        $data['tribute_requirement'] = $campaign_dto->tribute_requirement;
        $data['tribute_notification_preference'] = $campaign_dto->tribute_notification_preference;

        if (growfund_user()->is_logged_in()) {
            $current_user = growfund_user();

            $data['donor_first_name'] = $current_user->get_first_name();
            $data['donor_last_name'] = $current_user->get_last_name();
            $data['donor_email'] = $current_user->get_email();
            $data['donor_phone'] = $current_user->get_meta('phone');
        }

        if (!is_email($data['donor_email'])) {
            return $this->handle_validation_error([
                'donor_email' => __('Invalid email address', 'growfund')
            ], null, $data);
        }

        $validator = Validator::make($data, CreateDonationDTO::checkout_validation_rules());

        $validator->apply_if([
            'donor_first_name',
            'donor_last_name',
            'donor_email',
            'donor_phone'
        ], 'required|string', function () {
            return !growfund_user()->is_logged_in();
        });

        if ($validator->is_failed()) {
            return $this->handle_validation_error($validator->get_errors(), null, $data);
        }

        return $data;
    }

    protected function apply_wc_checkout($campaign_id, $reward_id = null, $amount = 0, $bonus_amount = 0)
    {
        if (!Woocommerce::is_active()) {
            return growfund_redirect(growfund_campaign_url($campaign_id));
        }

        if (!growfund_user()->is_logged_in() && !growfund_app()->is_donation_mode()) {
            return growfund_redirect(growfund_login_url(growfund_campaign_url($campaign_id)));
        }

        $wc_product_id = growfund_wc_product_id();

        if (! $wc_product_id) {
            throw new Exception(esc_html__('Product not found', 'growfund'), (int) Response::NOT_FOUND);
        }

        WC()->cart->empty_cart();

        $campaign = $this->campaign_service->get_by_id($campaign_id);

        if (!empty($reward_id)) {
            $reward = $this->reward_service->get_by_id($reward_id);
            $amount = Money::prepare_for_display($reward->amount);
        }

        $amount = $amount + Money::prepare_for_display($bonus_amount);

        if (growfund_app()->is_donation_mode()) {
            $amount = !empty($amount) ? $amount : 100; // Default donation amount
        }

        if (!$amount && !growfund_app()->is_donation_mode()) {
            throw new InvalidArgumentException(esc_html__('Amount is required', 'growfund'));
        }

        $contribution_info_dto = new WoocommerceContributionDTO();
        $contribution_info_dto->campaign_name = $campaign->title;
        $contribution_info_dto->campaign_id = (int) $campaign_id;
        $contribution_info_dto->contribution_amount = (float) $amount;
        $contribution_info_dto->reward_id = $reward_id ? (int) $reward_id : null;
        $contribution_info_dto->bonus_support_amount = (float) $bonus_amount;

        Woocommerce::unset_custom_cart_info_from_session();
        Woocommerce::set_custom_cart_info_to_session($contribution_info_dto);

        $added = WC()->cart->add_to_cart($wc_product_id);

        if ($added) {
            return Url::redirect(wc_get_checkout_url());
        }

        throw new Exception(esc_html__('Failed to connect to WooCommerce', 'growfund'));
    }
}
