<?php

namespace Growfund\DTO\Donation;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\Campaign\FundSelectionType;
use Growfund\Constants\Campaign\TributeNotificationPreference;
use Growfund\Constants\Status\DonationStatus;
use Growfund\Constants\Campaign\TributeNotificationType;
use Growfund\Constants\Campaign\TributeRequirement;
use Growfund\Core\AppSettings;
use Growfund\DTO\DTO;
use Growfund\Payments\DTO\PaymentMethodDTO;
use Growfund\PostTypes\Campaign;
use Growfund\Sanitizer;
use Growfund\Supports\Arr;
use Growfund\Supports\Payment;
use Growfund\Supports\PostMeta;
use Growfund\Validation\Rules\EmailRule;

/**
 * Data Transfer Object for Creating Donation
 *
 * @since 1.0.0
 */
class CreateDonationDTO extends DTO
{
    /** @var string */
    public $uid;

    /** @var int */
    public $campaign_id;

    /** @var int */
    public $fund_id;

    /** @var int|null */
    public $user_id;

    /** @var string|null */
    public $email;

    /** @var int */
    public $amount;

    /** @var string|null */
    public $tribute_type;

    /** @var string|null */
    public $tribute_salutation;

    /** @var string|null */
    public $tribute_to;

    /** @var string|null */
    public $tribute_notification_type;

    /** @var string|null */
    public $tribute_notification_recipient_name;

    /** @var string|null */
    public $tribute_notification_recipient_phone;

    /** @var string|null */
    public $tribute_notification_recipient_email;

    /** @var string|null */
    public $tribute_notification_recipient_address;

    /** @var string|null */
    public $notes;

    /** @var string */
    public $status;

    /** @var string|null */
    public $transaction_id;

    /** @var string|null */
    public $payment_engine;

    /** @var PaymentMethodDTO|null */
    public $payment_method;

    /** @var string|null */
    public $payment_status;

    /** @var bool */
    public $is_anonymous;

    /** @var bool */
    public $is_manual = false;

    /** @var string */
    public $created_at;

    /** @var int */
    public $created_by;

    /** @var string */
    public $updated_at;

    /** @var int */
    public $updated_by;

    /** @var \Growfund\DTO\User\UserDTO */
    public $user_info;

    /**
     * Return validation rules
     *
     * @return array
     */
    public static function validation_rules()
    {
        return array_merge(
            [
				'campaign_id' => 'required|string|post_exists:post_type=' . Campaign::NAME,
				'fund_id' => [
					function ($value, $key, $data) {
						$fund_selection_type = PostMeta::get((int) $data['campaign_id'], 'fund_selection_type') ?? FundSelectionType::DONOR_DECIDE;

						if (growfund_settings(AppSettings::CAMPAIGNS)->allow_fund() && $fund_selection_type === FundSelectionType::DONOR_DECIDE && empty($value)) {
							/* translators: %s: field name */
							return sprintf(__('The %s field is required.', 'growfund'), str_replace(['_', '.'], ' ', $key));
						}

						return true;
					},
					'integer',
				],
				'user_id' => 'nullable|user_exists|integer',
				'amount' => [
                    'required',
                    'float',
                    function ($value) {
                        if ((int) $value <= 0) {
                            return __('The amount is required.', 'growfund');
                        }

                        return true;
                    }
                ],
				'notes' => 'nullable|string',
				'status' => 'string|in:' . implode(',', DonationStatus::get_constant_values()),
				'is_anonymous' => 'boolean',
				'payment_method' => [
					'required',
					'string',
					function($value, $key) {
						if (!Payment::is_valid_payment_method($value)) {
							/* translators: %s: field name */
							return sprintf(__('The %s is not valid.', 'growfund'), str_replace('_', ' ', $key));
						}

						return true;
					}
				],
			],
            self::tribute_validation_rules()
        );
    }

    protected static function tribute_validation_rules() {
        if (!growfund_settings(AppSettings::CAMPAIGNS)->allow_tribute()) {
            return [];
        }

        $tribute_recipient_address_required_rules = 'required_if:tribute_notification_type,' . implode(';', [TributeNotificationType::POST_MAIL, TributeNotificationType::BOTH]);

        return [
            'tribute_type' => [
                'string',
                function ($value, $key, $data) {
                    $has_tribute = PostMeta::get((int) $data['campaign_id'], 'has_tribute');
                    $has_tribute = isset($has_tribute)
                        ? filter_var($has_tribute, FILTER_VALIDATE_BOOLEAN)
                        : false;

                    $tribute_options = PostMeta::get((int) $data['campaign_id'], 'tribute_options');
					$tribute_options = !empty($tribute_options)
                        ? maybe_unserialize($tribute_options)
                        : [];

                    $tribute_requirement = PostMeta::get((int) $data['campaign_id'], 'tribute_requirement') ?? TributeRequirement::OPTIONAL;

                    if ($has_tribute) {
                        if (empty($value) && $tribute_requirement === TributeRequirement::REQUIRED) {
                            return __('The tribute type is required.', 'growfund');
                        }

                        if (!empty($value)) {
                            $is_valid_type = Arr::make($tribute_options)->some(function($option) use ($value) {
								return $option['message'] === $value;
							});

                            if (!$is_valid_type) {
								return __('The tribute type is not valid.', 'growfund');
                            }
                        }
                    }

                    return true;
                }
            ],
            'tribute_salutation' => 'required_if_exists:tribute_type|string',
            'tribute_to' => 'required_if_exists:tribute_type|string',
            'tribute_notification_type' =>  [
                function ($value, $key, $data) {
                    $tribute_notification_preference = PostMeta::get((int) $data['campaign_id'], 'tribute_notification_preference') ?? TributeNotificationPreference::LET_DONOR_DECIDE;

                    if (!empty($data['tribute_type']) && $tribute_notification_preference === TributeNotificationPreference::LET_DONOR_DECIDE && empty($value)) {
                        return __('The tribute notification type is required.', 'growfund');
                    }

                    if (!empty($value) && !in_array($value, TributeNotificationType::get_constant_values(), true)) {
                        return __('The tribute notification type is not valid.', 'growfund');
                    }

                    return true;
                },
                'string',
            ],
            'tribute_notification_recipient_name' => 'required_if_exists:tribute_type|string',
            'tribute_notification_recipient_phone' => 'required_if_exists:tribute_type|string',
            'tribute_notification_recipient_email' => [
				function ($value, $key, $data) {
					if (!empty($data['tribute_type']) && empty($value)) {
						return __('The recipient email is required.', 'growfund');
					}

                    $email_rule = new EmailRule($key, $value, null, $data);

                    if (!empty($value) && !$email_rule->validate_rule()) {
                        return $email_rule->get_error_message();
                    }

                    return true;
				}
            ],
            'tribute_notification_recipient_address' =>  $tribute_recipient_address_required_rules . '|array',
            'tribute_notification_recipient_address.address' => $tribute_recipient_address_required_rules . '|string',
            'tribute_notification_recipient_address.address_2' => 'string',
            'tribute_notification_recipient_address.city' => $tribute_recipient_address_required_rules . '|string',
            'tribute_notification_recipient_address.state' => 'string',
            'tribute_notification_recipient_address.zip_code' => $tribute_recipient_address_required_rules . '|string',
            'tribute_notification_recipient_address.country'=> $tribute_recipient_address_required_rules . '|string',
        ];
    }


    /**
     * Return sanitization rules
     *
     * @return array
     */
    public static function sanitization_rules()
    {
        return [
            'campaign_id'                              => Sanitizer::INT,
            'fund_id'                                  => Sanitizer::INT,
            'user_id'                                  => Sanitizer::INT,
            'amount'                                   => Sanitizer::MONEY,
            'tribute_requirement'                      => Sanitizer::TEXT,
            'tribute_type'                             => Sanitizer::TEXT,
            'tribute_salutation'                       => Sanitizer::TEXT,
            'tribute_to'                               => Sanitizer::TEXT,
            'tribute_notification_type'                => Sanitizer::TEXT,
            'tribute_notification_recipient_name'      => Sanitizer::TEXT,
            'tribute_notification_recipient_phone'     => Sanitizer::TEXT,
            'tribute_notification_recipient_email'     => Sanitizer::EMAIL,
            'tribute_notification_recipient_address'   => Sanitizer::ARRAY,
            'tribute_notification_recipient_address.address'   => Sanitizer::TEXT,
            'tribute_notification_recipient_address.address_2'  => Sanitizer::TEXT,
            'tribute_notification_recipient_address.city'      => Sanitizer::TEXT,
            'tribute_notification_recipient_address.state'     => Sanitizer::TEXT,
            'tribute_notification_recipient_address.zip_code'  => Sanitizer::TEXT,
            'tribute_notification_recipient_address.country'   => Sanitizer::TEXT,
            'notes'                                    => Sanitizer::TEXTAREA,
            'status'                                   => Sanitizer::TEXT,
            'is_anonymous'                             => Sanitizer::BOOL,
            'payment_method'                           => Sanitizer::TEXT
        ];
    }

    /**
     * Return validation rules for checkout page
     *
     * @return array
     */
    public static function checkout_validation_rules()
    {
        $rules = self::validation_rules();

        unset($rules['user_id']);

        return array_merge($rules, [
            'billing_address' => 'required|array',
            'billing_address.address'  => 'required|string',
            'billing_address.address_2'  => 'string',
            'billing_address.city' => 'required|string',
            'billing_address.state' => 'string',
            'billing_address.zip_code'  => 'required|string',
            'billing_address.country'  => 'required|string',
            'contact_info' => 'required|array',
            'contact_info.email' => 'required|email',
            'contact_info.first_name' => 'required|string',
            'contact_info.last_name' => 'required|string',
        ]);
    }

    /**
     * Return sanitization rules for checkout page
     *
     * @return array
     */
    public static function checkout_sanitization_rules()
    {
        return array_merge(self::sanitization_rules(), [
            'billing_address'               => Sanitizer::ARRAY,
            'billing_address.address'       => Sanitizer::TEXT,
            'billing_address.address_2'     => Sanitizer::TEXT,
            'billing_address.city'          => Sanitizer::TEXT,
            'billing_address.state'         => Sanitizer::TEXT,
            'billing_address.zip_code'      => Sanitizer::TEXT,
            'billing_address.country'       => Sanitizer::TEXT,
            'contact_info'                  => Sanitizer::ARRAY,
            'contact_info.email'            => Sanitizer::EMAIL,
            'contact_info.first_name'       => Sanitizer::TEXT,
            'contact_info.last_name'        => Sanitizer::TEXT,
        ]);
    }
}
