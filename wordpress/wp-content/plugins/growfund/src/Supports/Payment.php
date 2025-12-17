<?php

namespace Growfund\Supports;

defined( 'ABSPATH' ) || exit;

use Exception;
use Growfund\Core\AppSettings;
use Growfund\Payments\Constants\PaymentGatewayType;
use Growfund\Payments\Contracts\FuturePaymentContract;
use Growfund\Payments\DTO\PaymentMethodDTO;

class Payment
{
    public static function get_engine()
    {
        return growfund_settings(AppSettings::PAYMENT)->get_payment_engine();
    }

    public static function get_installed_payment_methods()
    {
        return growfund_settings(AppSettings::PAYMENT)->get_installed_payment_gateways();
    }

    /**
     * Get all the active payment methods
     *
     * @return array
     */
    public static function get_active_payment_methods($type = null) // phpcs:ignore
    {
        $installed_payment_methods = static::get_installed_payment_methods();

        return Arr::make($installed_payment_methods)->filter(function ($method) {
            return $method->is_enabled;
        })->toArray();
    }

    public static function is_valid_payment_method($name)
    {
        $result = array_filter(static::get_installed_payment_methods(), function ($method) use ($name) {
            return $method->name === $name;
        });

        return !empty($result) && count($result) > 0;
    }

    public static function is_manual_payment_method($name)
    {
        foreach (static::get_installed_payment_methods() as $payment_method) {
            if ($payment_method->name === $name && $payment_method->type === PaymentGatewayType::MANUAL) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the payment method information by its name.
     *
     * @param string $name
     * @return PaymentMethodDTO|null
     */
    public static function get_payment_method_by_name(string $name)
    {
        if (empty($name)) {
            return null;
        }

        $payments = growfund_settings(AppSettings::PAYMENT)->get('payments', []);
        $payments = Arr::make($payments);

        $payment = $payments->find(function ($item) use ($name) {
            return $item['name'] === $name;
        });

        if (empty($payment)) {
            return null;
        }

        $logo = $payment['config']['logo'] ?? null;

        if (!empty($logo) && is_numeric($logo)) {
            $logo = MediaAttachment::make($logo);
        }

        $src = is_string($logo) ? $logo : ($logo['url'] ?? null);

        $payment['config']['logo'] = $logo;

        $dto = new PaymentMethodDTO();
        $dto->name = $payment['name'];
        $dto->type = $payment['type'];
        $dto->logo = $src;
        $dto->label = $payment['config']['label'];
        $dto->instruction = $payment['config']['instruction'] ?? null;

        return $dto;
    }

	/**
	 * Check if a payment gateway supports future payments
	 *
	 * @param string $name The name of the payment gateway
	 * @return bool True if the payment gateway supports future payments, false otherwise
	 */
    public static function support_future_payment(string $name)
    {
        try {
            $payment_gateway = growfund_payment_gateway($name);
        } catch (Exception $error) {
            return false;
        }

        if (!$payment_gateway instanceof FuturePaymentContract) {
            return false;
        }
        
        return true;
    }
}
