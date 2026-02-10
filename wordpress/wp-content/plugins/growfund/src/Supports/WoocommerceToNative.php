<?php

namespace Growfund\Supports;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\Status\DonationStatus;
use Growfund\Constants\Status\PaymentStatus;
use Growfund\Constants\Status\PledgeStatus;

class WoocommerceToNative
{
    public static function donation_status($status)
    {
        switch ($status) {
            case 'completed':
            case 'wc-completed':
            case 'processing':
            case 'wc-processing':
                return DonationStatus::COMPLETED;
            case 'cancelled':
            case 'wc-cancelled':
                return DonationStatus::FAILED;
            case 'on-hold':
            case 'wc-on-hold':
            case 'pending':
            case 'wc-pending':
                return DonationStatus::PENDING;
            case 'refunded':
            case 'wc-refunded':
                return DonationStatus::REFUNDED;
            default:
                return DonationStatus::PENDING;
        }
    }

    public static function pledge_status($status)
    {
        switch ($status) {
            case 'completed':
            case 'wc-completed':
                return PledgeStatus::COMPLETED;
            case 'processing':
            case 'wc-processing':
                return PledgeStatus::BACKED;
            case 'cancelled':
            case 'wc-cancelled':
                return PledgeStatus::FAILED;
            case 'on-hold':
            case 'wc-on-hold':
            case 'pending':
            case 'wc-pending':
                return PledgeStatus::PENDING;
            case 'refunded':
            case 'wc-refunded':
                return PledgeStatus::REFUNDED;
            default:
                return PledgeStatus::PENDING;
        }
    }

    public static function contribution_status($status)
    {
        if (growfund_app()->is_donation_mode()) {
            return self::donation_status($status);
        }

        return self::pledge_status($status);
    }

    public static function payment_status($status)
    {
        switch ($status) {
            case 'completed':
            case 'wc-completed':
            case 'processing':
            case 'wc-processing':
                return PaymentStatus::PAID;
            case 'cancelled':
            case 'wc-cancelled':
                return PaymentStatus::FAILED;
            case 'on-hold':
            case 'wc-on-hold':
            case 'pending':
            case 'wc-pending':
                return PaymentStatus::PENDING;
            case 'refunded':
            case 'wc-refunded':
                return PaymentStatus::REFUNDED;
            default:
                return PaymentStatus::UNPAID;
        }
    }
}
