<?php

namespace Growfund\Supports;

defined( 'ABSPATH' ) || exit;

use Exception;
use Growfund\Constants\PaymentEngine;
use Growfund\Core\AppSettings;
use Growfund\Payments\Constants\PaymentGatewayType;
use Growfund\Payments\Contracts\FuturePaymentContract;
use Growfund\Payments\DTO\PaymentGatewayDTO;
use Growfund\Payments\DTO\PaymentMethodDTO;

class Payment
{
    public static function get_engine()
    {
        return growfund_settings(AppSettings::PAYMENT)->get_payment_engine();
    }

    /**
     * @return PaymentGatewayDTO[]
     */
    public static function get_installed_payment_methods()
    {
        if (growfund_payment_engine() === PaymentEngine::WOOCOMMERCE && function_exists('WC')) {
            $wc_payment_gateways = WC()->payment_gateways->get_available_payment_gateways();
            $offline_gateways = ['cod', 'bacs', 'cheque'];

            $payment_gateways = [];

            foreach ($wc_payment_gateways as $gateway) {
                $type = in_array($gateway->id, $offline_gateways, true) ? PaymentGatewayType::MANUAL : PaymentGatewayType::ONLINE;
                $payment_gateways[] = new PaymentGatewayDTO([
                    'name' => $gateway->id,
                    'type' => $type,
                    'config' => [
                        'label' => $gateway->method_title ?? $gateway->id,
                        'logo' => null,
                        'title' => $gateway->method_title ?? $gateway->id,
                        'instruction' => $gateway->instructions ?? ''
                    ],
                    'is_enabled' => $gateway->enabled === 'yes',
                    'is_installed' => $gateway->enabled === 'yes'
                ]);
			}

            return $payment_gateways;
        }
        

        return growfund_settings(AppSettings::PAYMENT)->get_installed_payment_gateways();
    }

    /**
     * Get all the active payment methods
     *
     * @return \Growfund\Payments\DTO\PaymentGatewayDTO[]
     */
    public static function get_active_payment_methods($type = null)
    {
        $installed_payment_methods = static::get_installed_payment_methods();

        return Arr::make($installed_payment_methods)->filter(function ($method) use ($type) {
            if (!empty($type)) {
                return $method->type === $type && $method->is_enabled;
            }

            return $method->is_enabled;
        })->toArray();
    }

    /** 
     * @return bool
     */
    public static function is_valid_payment_method($name)
    {
        $result = array_filter(static::get_installed_payment_methods(), function ($method) use ($name) {
            return $method->name === $name;
        });

        return !empty($result) && count($result) > 0;
    }

    /** 
     * @return bool
     */
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

        $installed_payment_methods = static::get_installed_payment_methods();
        $payments = Arr::make($installed_payment_methods);

        $payment = $payments->find(function ($item) use ($name) {
            return $item->name === $name;
        });

        if (empty($payment)) {
            return null;
        }

        $payment = $payment->to_array();

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
