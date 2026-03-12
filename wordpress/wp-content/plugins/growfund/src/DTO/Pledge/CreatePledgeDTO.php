<?php

namespace Growfund\DTO\Pledge;

defined( 'ABSPATH' ) || exit;

use Growfund\CastAttributes\DateTimeAttribute;
use Growfund\CastAttributes\MoneyAttribute;
use Growfund\Constants\PaymentEngine;
use Growfund\Constants\Pledge\DeliveryOption;
use Growfund\Constants\Pledge\PledgeOption;
use Growfund\Constants\Reward\RewardType;
use Growfund\Constants\Status\PledgeStatus;
use Growfund\Core\AppSettings;
use Growfund\DTO\DTO;
use Growfund\Payments\DTO\PaymentMethodDTO;
use Growfund\PostTypes\Campaign;
use Growfund\PostTypes\Reward;
use Growfund\Sanitizer;
use Growfund\Supports\Payment;
use Growfund\Supports\PostMeta;

/**
 * Data Transfer Object for Creating Pledge
 *
 * @since 1.0.0
 */
class CreatePledgeDTO extends DTO
{
    /**
     * Fields that are considered not part of "meta" data.
     *
     * @var array
     */
    protected static $base_fields = [];

    protected $casts = [
        'amount'                     => MoneyAttribute::class,
        'bonus_support_amount'       => MoneyAttribute::class,
        'created_at'                 => DateTimeAttribute::class,
        'updated_at'                 => DateTimeAttribute::class,
    ];

    /** @var string */
    public $uid;

    /** @var int */
    public $campaign_id;

    /** @var int|null */
    public $user_id;

    /** @var string|null */
    public $email;

    /** @var string */
    public $status;

    /** @var bool */
    public $is_manual = false;

    /** @var string */
    public $pledge_option;

    /** @var int|null */
    public $reward_id;

    /** @var int */
    public $amount;

    /** @var int|null */
    public $bonus_support_amount;

    public $shipping_cost;

    /** @var string|null */
    public $transaction_id;

    /** @var string|null */
    public $payment_engine;

    /** @var \Growfund\Constants\Status\PaymentStatus|null */
    public $payment_status;

    /** @var PaymentMethodDTO|null */
    public $payment_method;

    /** @var \Growfund\Constants\Pledge\DeliveryOption */
    public $delivery_option;

    /** @var PledgeRewardDTO */
    public $reward_info;

    /** @var PledgeBackerDTO */
    public $user_info;

    /** @var string|null */
    public $created_at;

    /** @var string|null */
    public $created_by;

    /** @var string|null */
    public $updated_at;

    /** @var string|null */
    public $updated_by;

    /** @var string|null */
    public $notes;


    /**
     * Return validation rules
     *
     * @return array
     */
    public static function validation_rules(): array
    {
        return [
            'campaign_id'                => 'required|post_exists:post_type=' . Campaign::NAME,
            'user_id'                    => 'integer',
            'status'                     => 'required|string|in:' . implode(',', PledgeStatus::get_constant_values()),
            'pledge_option'              => 'required|string|in:' . implode(',', PledgeOption::get_constant_values()),
            'reward_id'                  => 'required_if:pledge_option,' . PledgeOption::WITH_REWARDS . '|post_exists:post_type=' . Reward::NAME,
            'delivery_option'            => static::get_delivery_option_rules(),
            'amount'                     => 'required_if:pledge_option,' . PledgeOption::WITHOUT_REWARDS . '|float|min:0',
            'bonus_support_amount'       => 'float|min:0',
            'notes'                      => 'string',
            'is_manual'                  => 'boolean',
            'payment_method'             => [
                'required',
                'string',
                function($value, $key) {
                    if (!Payment::is_valid_payment_method($value)) {
                        /* translators: %s: field name */
                        return sprintf(__('The %s is not valid.', 'growfund'), $key);
                    }

                    if (!Payment::is_active_payment_method($value)) {
							/* translators: %s: field name */
							return sprintf(__('The %s is not active.', 'growfund'), str_replace('_', ' ', $key));
					}

					if (!Payment::is_configured_payment_method($value)) {
						/* translators: %s: field name */
						return sprintf(__('The %s is not configured. please contact with administrator.', 'growfund'), str_replace('_', ' ', $key));
					}

                    return true;
                }
            ],
        ];
    }

    /**
     * Return sanitization rules
     *
     * @return array
     */
    public static function sanitization_rules(): array
    {
        return [
            'campaign_id'                => Sanitizer::INT,
            'user_id'                    => Sanitizer::INT,
            'status'                     => Sanitizer::TEXT,
            'pledge_option'              => Sanitizer::TEXT,
            'reward_id'                  => Sanitizer::INT,
            'delivery_option'            => Sanitizer::TEXT,
            'amount'                     => Sanitizer::MONEY,
            'bonus_support_amount'       => Sanitizer::MONEY,
            'notes'                      => Sanitizer::TEXTAREA,
            'is_manual'                  => Sanitizer::BOOL,
            'payment_method'             => Sanitizer::TEXT,
        ];
    }

    /**
     * Return sanitization rules for checkout
     *
     * @return array
     */
    public static function checkout_sanitization_rules(): array
    {
        return [
            'campaign_id'                => Sanitizer::INT,
            'pledge_option'              => Sanitizer::TEXT,
            'reward_id'                  => Sanitizer::INT,
            'delivery_option'            => Sanitizer::TEXT,
            'amount'                     => Sanitizer::MONEY,
            'bonus_support_amount'       => Sanitizer::MONEY,
            'notes'                      => Sanitizer::TEXTAREA,
            'payment_method'             => Sanitizer::TEXT,
            'shipping_address'              => Sanitizer::ARRAY,
            'shipping_address.address'      => Sanitizer::TEXT,
            'shipping_address.address_2'    => Sanitizer::TEXT,
            'shipping_address.city'         => Sanitizer::TEXT,
            'shipping_address.state'        => Sanitizer::TEXT,
            'shipping_address.zip_code'     => Sanitizer::TEXT,
            'shipping_address.country'      => Sanitizer::TEXT,
            'billing_address'               => Sanitizer::ARRAY,
            'billing_address.address'       => Sanitizer::TEXT,
            'billing_address.address_2'     => Sanitizer::TEXT,
            'billing_address.city'          => Sanitizer::TEXT,
            'billing_address.state'         => Sanitizer::TEXT,
            'billing_address.zip_code'      => Sanitizer::TEXT,
            'billing_address.country'       => Sanitizer::TEXT,
            'is_billing_address_same'       => Sanitizer::BOOL,
        ];
    }

    /**
     * Return validation rules for checkout
     *
     * @return array
     */
    public static function checkout_validation_rules(): array
    {
        return [
            'campaign_id'                => 'required|post_exists:post_type=' . Campaign::NAME,
            'pledge_option'              => 'required|string|in:' . implode(',', PledgeOption::get_constant_values()),
            'reward_id'                  => 'required_if:pledge_option,' . PledgeOption::WITH_REWARDS . '|post_exists:post_type=' . Reward::NAME,
            'delivery_option'            => static::get_delivery_option_rules(),
            'amount'                     => [
                'required_if:pledge_option,' . PledgeOption::WITHOUT_REWARDS,
                'float',
                function($value, $key, $data) {
                    if ($data['pledge_option'] === PledgeOption::WITHOUT_REWARDS && (int) $value <= 0) {
                        return __('The amount is required.', 'growfund');
                    }

                    return true;
                }
            ],
            'bonus_support_amount' => 'float|min:0',
            'notes' => 'string',
            'payment_method' => [
				function($value, $key) {
					if (growfund_settings(AppSettings::PAYMENT)->get_payment_engine() === PaymentEngine::NATIVE) {
                        if (empty($value)) {
							return __('The payment method is required.', 'growfund');
						}
                    
                        if (!Payment::is_valid_payment_method($value)) {
                            /* translators: %s: field name */
                            return sprintf(__('The %s is not valid.', 'growfund'), str_replace('_', ' ', $key));
                        }

                        if (!Payment::is_active_payment_method($value)) {
							/* translators: %s: field name */
							return sprintf(__('The %s is not active.', 'growfund'), str_replace('_', ' ', $key));
						}

                        if (!Payment::is_configured_payment_method($value)) {
							/* translators: %s: field name */
							return sprintf(__('The %s is not configured. please contact with administrator.', 'growfund'), str_replace('_', ' ', $key));
						}
					}

					return true;
				},
                'string'
			],
            'shipping_address' => [
                'array',
                function($value, $key, $data) {
                    if (
                        empty($value)
                        && $data['pledge_option'] === PledgeOption::WITH_REWARDS 
                        && PostMeta::get($data['reward_id'], 'reward_type') !== RewardType::DIGITAL_GOODS
                        && $data['delivery_option'] !== DeliveryOption::LOCAL_PICKUP
                    ) {
                        return __('The shipping address is required.', 'growfund'); 
                    }

                    return true;
                }
            ],
            'shipping_address.address' => [
                'string',
                function($value, $key, $data) {
                    if (empty($value) && !empty($data['shipping_address']) && $data['delivery_option'] !== DeliveryOption::LOCAL_PICKUP) {
                        return __('The shipping address is required.', 'growfund');
                    }

                    return true;
                }
            ],
            'shipping_address.address_2' => 'string',
            'shipping_address.city' => [
                'string',
                function($value, $key, $data) {
                    if (empty($value) && !empty($data['shipping_address']) && $data['delivery_option'] !== DeliveryOption::LOCAL_PICKUP) {
                        return __('The shipping city is required.', 'growfund');
                    }

                    return true;
                }
            ],
            'shipping_address.state' => 'string',
            'shipping_address.zip_code' => [
                'string',
                function($value, $key, $data) {
                    if (empty($value) && !empty($data['shipping_address']) && $data['delivery_option'] !== DeliveryOption::LOCAL_PICKUP) {
                        return __('The shipping country is required.', 'growfund');
                    }

                    return true;
                }
            ],
            'shipping_address.country' => [
                'string',
                function($value, $key, $data) {
                    if (empty($value) && !empty($data['shipping_address']) && $data['delivery_option'] !== DeliveryOption::LOCAL_PICKUP) {
                        /* translators: %s: field name */
                        return sprintf(__('The %s is required.', 'growfund'), str_replace('.', ' ', $key));
                    }

                    return true;
                }
            ],
            'billing_address' => [
                'array',
                function ($value, $key, $data) {
                    if ($data['delivery_option'] === DeliveryOption::LOCAL_PICKUP && empty($value)) {
                        return __('The billing address is required.', 'growfund');
                    }

                    if (empty($data['is_billing_address_same'] ?? false) && empty($value)) {
						return __('The bulling address is required.', 'growfund');
                    }

                    return true;
                }
            ],
            'billing_address.address'  => [
                'string',
                function ($value, $key, $data) {
                    if ($data['delivery_option'] === DeliveryOption::LOCAL_PICKUP && empty($value)) {
                        return __('The billing address is required.', 'growfund');
                    }

                    if (empty($data['is_billing_address_same'] ?? false) && empty($value)) {
                        return __('The billing address is required.', 'growfund');
                    }

                    return true;
                }
            ],
            'billing_address.address_2'  => 'string',
            'billing_address.city' => [
                'string',
                function ($value, $key, $data) {
                    if ($data['delivery_option'] === DeliveryOption::LOCAL_PICKUP && empty($value)) {
                        return __('The billing city is required.', 'growfund');
                    }

                    if (empty($data['is_billing_address_same'] ?? false) && empty($value)) {
                        return __('The billing city is required.', 'growfund');
                    }

                    return true;
                }
            ],
            'billing_address.state' => 'string',
            'billing_address.zip_code'  => [
                'string',
                function ($value, $key, $data) {
                    if ($data['delivery_option'] === DeliveryOption::LOCAL_PICKUP && empty($value)) {
                        return __('The billing zip/postal code is required.', 'growfund');
                    }

                    if (empty($data['is_billing_address_same'] ?? false) && empty($value)) {
                        return __('The billing zip/postal code is required.', 'growfund');
                    }

                    return true;
                }
            ],
            'billing_address.country'  => [
                'string',
                function ($value, $key, $data) {
                    if ($data['delivery_option'] === DeliveryOption::LOCAL_PICKUP && empty($value)) {
                        return __('The billing country is required.', 'growfund');
                    }
                    
                    if (empty($data['is_billing_address_same'] ?? false) && empty($value)) {
                        return __('The billing country is required.', 'growfund');
                    }

                    return true;
                }
            ],
            'is_billing_address_same' => 'boolean',
        ];
    }

    /**
     * Get delivery option rules.
     *
     * @return array
     */
    protected static function get_delivery_option_rules(): array
    {
        return [
			'string',
			function($value, $key, $data) {
				if (
					$data['pledge_option'] === PledgeOption::WITH_REWARDS 
					&& PostMeta::get($data['reward_id'], 'reward_type') !== RewardType::DIGITAL_GOODS
				) {
					if (empty($value)) {
						return __('The delivery option is required.', 'growfund');
					}

					if (!in_array($value, DeliveryOption::get_constant_values(), true)) {
						return __('The delivery option is not valid.', 'growfund');
					}

					$allow_local_pickup = filter_var(PostMeta::get($data['reward_id'], 'allow_local_pickup') ?? false, FILTER_VALIDATE_BOOLEAN);

					if ($value === DeliveryOption::LOCAL_PICKUP && !$allow_local_pickup) {
						return __('The reward does not allow local pickup.', 'growfund');
					}
				}
                    
				return true;
			}
		];
    }
}
