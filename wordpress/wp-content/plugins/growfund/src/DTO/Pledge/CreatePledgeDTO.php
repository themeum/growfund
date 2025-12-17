<?php

namespace Growfund\DTO\Pledge;

defined( 'ABSPATH' ) || exit;

use Growfund\CastAttributes\DateTimeAttribute;
use Growfund\CastAttributes\MoneyAttribute;
use Growfund\Constants\PledgeOption;
use Growfund\Constants\Status\PledgeStatus;
use Growfund\DTO\DTO;
use Growfund\Payments\DTO\PaymentMethodDTO;
use Growfund\PostTypes\Campaign;
use Growfund\PostTypes\Reward;
use Growfund\Sanitizer;

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
    public $is_manual;

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
            'amount'                     => 'required_if:pledge_option,' . PledgeOption::WITHOUT_REWARDS . '|float|min:0',
            'bonus_support_amount'       => 'float|min:0',
            'notes'                      => 'string',
            'is_manual'                  => 'boolean',
            'payment_method'             => 'required|string',
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
            'amount'                     => Sanitizer::MONEY,
            'bonus_support_amount'       => Sanitizer::MONEY,
            'notes'                      => Sanitizer::TEXTAREA,
            'is_manual'                  => Sanitizer::BOOL,
            'payment_method'             => Sanitizer::TEXT,
            'country'                    => Sanitizer::TEXT,
            'address'                    => Sanitizer::TEXT,
            'address_2'                  => Sanitizer::TEXT,
            'city'                       => Sanitizer::TEXT,
            'state'                      => Sanitizer::TEXT,
            'zip_code'                   => Sanitizer::TEXT,
        ];
    }

    /**
     * Return validation rules for checkout page
     *
     * @return array
     */
    public static function checkout_validation_rules(): array
    {
        return [
            'campaign_id'                => 'required|post_exists:post_type=' . Campaign::NAME,
            'reward_id'                  => 'nullable',
            'pledge_option'              => 'required|string|in:' . PledgeOption::WITH_REWARDS . ',' . PledgeOption::WITHOUT_REWARDS,
            'amount'                     => 'required_if:pledge_option,' . PledgeOption::WITHOUT_REWARDS . '|float|min:1',
            'bonus_support_amount'       => 'nullable|float|min:0',
            'notes'                      => 'nullable|string',
            'payment_method'             => 'required|string',
            'country'                    => 'required_if:pledge_option,' . PledgeOption::WITH_REWARDS . '|string',
            'address'                    => 'required_if:pledge_option,' . PledgeOption::WITH_REWARDS . '|string',
            'city'                       => 'required_if:pledge_option,' . PledgeOption::WITH_REWARDS . '|string',
            'zip_code'                   => 'required_if:pledge_option,' . PledgeOption::WITH_REWARDS . '|string',
            'state'                      => 'required_if:pledge_option,' . PledgeOption::WITH_REWARDS . '|string',
            'address_2'                  => 'nullable|string',
        ];
    }
}
