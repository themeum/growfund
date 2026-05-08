<?php

namespace Growfund\DTO\Pledge;

defined( 'ABSPATH' ) || exit;

use Growfund\CastAttributes\MoneyAttribute;
use Growfund\DTO\DTO;
use Growfund\Payments\DTO\PaymentMethodDTO;
use Growfund\Supports\PriceCalculator;

/**
 * Data Transfer Object for PledgeCampaign
 *
 * @since 1.0.0
 */
class PledgePaymentDTO extends DTO
{
    /**
     * Fields that are considered not part of "meta" data.
     *
     * @var array
     */
    protected static $base_fields = [];

    /** @var float|null */
    public $amount;

    /** @var float|null */
    public $bonus_support_amount;

    /** @var float|null */
    public $shipping_cost;

    /** @var float|null */
    public $recovery_fee;

    /** @var float|null */
    public $total;

    /** @var string|null */
    public $transaction_id;

    /** @var PaymentMethodDTO */
    public $payment_method;

    /** @var string */
    public $payment_status;

    /** @var int */
    public $raw_total;

    protected function get_casts()
    {
        return [
            'raw_total' => function () {
                return $this->get_raw_total_amount();
            },
            'amount' => MoneyAttribute::class,
            'bonus_support_amount' => MoneyAttribute::class,
            'shipping_cost' => MoneyAttribute::class,
            'recovery_fee' => MoneyAttribute::class,
            'total' => function () {
                return $this->get_total_amount();
            },
            'payment_method' => PaymentMethodDTO::class
        ];
    }

    public function get_total_amount()
    {
        return PriceCalculator::calculate_pledge_total_amount(
            $this->amount, 
            $this->shipping_cost, 
            $this->bonus_support_amount, 
            $this->recovery_fee
        );
    }

    public function get_raw_total_amount()
    {
        if (!empty($this->raw_total)) {
            return $this->raw_total;
        }

        $this->raw_total = $this->amount + $this->shipping_cost + $this->bonus_support_amount;

        return $this->raw_total;
    }
}
