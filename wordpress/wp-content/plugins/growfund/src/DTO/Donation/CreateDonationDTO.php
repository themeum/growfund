<?php

namespace Growfund\DTO\Donation;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\Campaign\FundSelectionType;
use Growfund\Constants\Status\DonationStatus;
use Growfund\Constants\TributeNotificationType;
use Growfund\DTO\DTO;
use Growfund\Payments\DTO\PaymentMethodDTO;
use Growfund\PostTypes\Campaign;
use Growfund\Sanitizer;
use Growfund\Supports\PostMeta;

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

    /** @var string|null */
    public $payment_method;

    /** @var PaymentMethodDTO|null */
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
    public static function validation_rules(): array
    {
        return [
            'campaign_id'                              => 'required|string|post_exists:post_type=' . Campaign::NAME,
            'fund_id'                                  => [
				function ($value, $key, $data) {
                    $fund_selection_type = PostMeta::get($data['campaign_id'], 'fund_selection_type');
					if ($fund_selection_type === FundSelectionType::DONOR_DECIDE && empty($value)) {
						/* translators: %s: field name */
						return sprintf(__('The %s field is required.', 'growfund'), $key);
					}

					return true;
				},
				'integer',
			],
            'user_id'                                  => 'nullable|user_exists|integer',
            'amount'                                   => 'required|integer|min:1',
            'tribute_type'                             => 'required_if_exists:tribute_notification_type|string',
            'tribute_salutation'                       => 'required_if_exists:tribute_type|string',
            'tribute_to'                               => 'required_if_exists:tribute_type|string',
            'tribute_notification_type'                => 'required_if_exists:tribute_type|string|in:' . implode((','), TributeNotificationType::get_constant_values()),
            'tribute_notification_recipient_name'      => 'required_if_exists:tribute_type|string',
            'tribute_notification_recipient_phone'     => 'required_if:tribute_notification_type,' . implode(';', [TributeNotificationType::ECARD, TributeNotificationType::BOTH]) . '|string',
            'tribute_notification_recipient_email'     => 'required_if:tribute_notification_type,' . implode(';', [TributeNotificationType::ECARD, TributeNotificationType::BOTH]) . '|email',
            'tribute_notification_recipient_address'   => 'required_if:tribute_notification_type,' . implode(';', [TributeNotificationType::POST_MAIL, TributeNotificationType::BOTH]) . '|array',
            'tribute_notification_recipient_address.address'     => 'required_if:tribute_notification_type,' . implode(';', [TributeNotificationType::POST_MAIL, TributeNotificationType::BOTH]) . '|string',
            'tribute_notification_recipient_address.address2'    => 'string',
            'tribute_notification_recipient_address.city'        => 'required_if:tribute_notification_type,' . implode(';', [TributeNotificationType::POST_MAIL, TributeNotificationType::BOTH]) . '|string',
            'tribute_notification_recipient_address.state'       => 'string',
            'tribute_notification_recipient_address.zip_code'    => 'required_if:tribute_notification_type,' . implode(';', [TributeNotificationType::POST_MAIL, TributeNotificationType::BOTH]) . '|string',
            'tribute_notification_recipient_address.country'     => 'required_if:tribute_notification_type,' . implode(';', [TributeNotificationType::POST_MAIL, TributeNotificationType::BOTH]) . '|string',
            'notes'                                    => 'nullable|string',
            'status'                                   => 'required|string|in:' . implode(',', DonationStatus::get_constant_values()),
            'is_anonymous'                             => 'required|boolean',
            'payment_method'                           => 'required|string',
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
            'campaign_id'                              => Sanitizer::INT,
            'fund_id'                                  => Sanitizer::INT,
            'user_id'                                  => Sanitizer::INT,
            'amount'                                   => Sanitizer::MONEY,
            'dedicate_donation'                        => Sanitizer::TEXT,
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
            'tribute_notification_recipient_address.address2'  => Sanitizer::TEXT,
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
    public static function checkout_validation_rules(): array
    {
        return [
            'campaign_id'                                    => 'required|integer|post_exists:post_type=' . Campaign::NAME,
            'fund_id'                                        => [
				function ($value, $key, $data) {
                    $fund_selection_type = PostMeta::get($data['campaign_id'], 'fund_selection_type');
					if ($fund_selection_type === FundSelectionType::DONOR_DECIDE && empty($value)) {
						/* translators: %s: field name */
						return sprintf(__('The %s field is required.', 'growfund'), $key);
					}

					return true;
				},
				'integer',
			],
            'amount'                                         => 'required|integer|min:1',
            'dedicate_donation'                              => 'nullable|string',
            'tribute_requirement'                            => 'tribute_fields',
            'tribute_notification_preference'                => 'tribute_fields',
            'tribute_type'                                   => 'tribute_fields',
            'tribute_salutation'                             => 'tribute_fields',
            'tribute_first_name'                             => 'tribute_fields',
            'tribute_last_name'                              => 'tribute_fields',
            'tribute_notification_type'                      => 'tribute_fields',
            'tribute_notification_recipient_name'            => 'tribute_fields',
            'tribute_notification_recipient_phone'           => 'tribute_fields',
            'tribute_notification_recipient_email'           => 'tribute_fields',
            'tribute_notification_recipient_address'         => 'tribute_fields',
            'tribute_notification_recipient_address.address' => 'tribute_fields',
            'tribute_notification_recipient_address.address2' => 'tribute_fields',
            'tribute_notification_recipient_address.city'     => 'tribute_fields',
            'tribute_notification_recipient_address.state'    => 'tribute_fields',
            'tribute_notification_recipient_address.zip_code' => 'tribute_fields',
            'tribute_notification_recipient_address.country' => 'tribute_fields',
            'notes'                                          => 'nullable|string',
            'payment_method'                                 => 'required|string',
            'is_anonymous'                                   => 'boolean',
        ];
    }
}
