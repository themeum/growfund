<?php

namespace Growfund\DTO\Donation;

defined( 'ABSPATH' ) || exit;

use Growfund\CastAttributes\ArrayAttribute;
use Growfund\CastAttributes\BooleanAttribute;
use Growfund\CastAttributes\DateTimeAttribute;
use Growfund\CastAttributes\MoneyAttribute;
use Growfund\DTO\DTO;
use Growfund\Payments\DTO\PaymentMethodDTO;

/**
 * Data Transfer Object for a Donation record
 *
 * @since 1.0.0
 */
class AnnualReceiptDonationDTO extends DTO
{
    protected $casts = [
        'amount' => MoneyAttribute::class,
        'is_anonymous' => BooleanAttribute::class,
        'is_manual' => BooleanAttribute::class,
        'tribute_notification_recipient_address' => ArrayAttribute::class,
        'created_at' => DateTimeAttribute::class,
        'updated_at' => DateTimeAttribute::class,
        'payment_method' => PaymentMethodDTO::class,
    ];

    /** @var string */
    public $id;

    /** @var string */
    public $uid;

    /** @var int */
    public $amount;

    /** @var string */
    public $tribute_type;

    /** @var string */
    public $tribute_salutation;

    /** @var string */
    public $tribute_to;

    /** @var string */
    public $tribute_notification_type;

    /** @var string */
    public $tribute_notification_recipient_name;

    /** @var string */
    public $tribute_notification_recipient_phone;

    /** @var string */
    public $tribute_notification_recipient_email;

    /** @var array */
    public $tribute_notification_recipient_address;

    /** @var string|null */
    public $notes;

    /** @var string */
    public $status;

    /** @var int|null */
    public $transaction_id;

    /** @var PaymentMethodDTO */
    public $payment_method;

    /** @var string */
    public $payment_status;

    /** @var bool */
    public $is_anonymous;

    /** @var bool */
    public $is_manual;

    /** @var string */
    public $created_at;

    /** @var string */
    public $updated_at;
}
