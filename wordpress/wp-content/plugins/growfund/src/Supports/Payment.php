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
use Throwable;

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
        if (growfund_payment_engine() === PaymentEngine::WOOCOMMERCE) {
            return static::get_woocommerce_manual_payment_methods();
        }
        

        return growfund_settings(AppSettings::PAYMENT)->get_installed_payment_gateways();
    }

    /**
     * @return PaymentGatewayDTO[]
     */
    public static function get_woocommerce_manual_payment_methods() {
        if (!Woocommerce::is_active() || !Woocommerce::is_payment_gateways_loaded()) {
            return [];   
        }

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

    public static function is_woocommerce_manual_payment_method($name) {
        return Arr::make(static::get_woocommerce_manual_payment_methods())->some(function ($method) use ($name) {
            return $method->name === $name;
        });
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
        return Arr::make(static::get_installed_payment_methods())->some(function ($method) use ($name) {
            return $method->name === $name;
        });
    }

    /** 
     * @return bool
     */
    public static function is_active_payment_method($name)
    {
        return Arr::make(static::get_active_payment_methods())->some(function ($method) use ($name) {
            return $method->name === $name;
        });
    }

    /** 
     * @return bool
     */
    public static function is_configured_payment_method($name)
    {
        if (growfund_payment_engine() === PaymentEngine::WOOCOMMERCE) {
            return true;
        }

        if (!static::is_active_payment_method($name)) {
            return false;
        }

        if (static::is_manual_payment_method($name)) {
            $payment_method = Arr::make(static::get_installed_payment_methods())->find(function ($method) use ($name) {
				return $method->name === $name && $method->type === PaymentGatewayType::MANUAL;
			});

            if (empty($payment_method) || empty($payment_method->config['instruction'] ?? '')) {
                return false;
            }

            return true;
        }

        $payment_gateway = growfund_payment_gateway($name, false);

        if (empty($payment_gateway)) {
            return false;
        }

        try {
            return $payment_gateway->is_configured() ?? false;
        } catch (Throwable $_) {
            return false;
        }
    }

    /** 
     * @return bool
     */
    public static function is_manual_payment_method($name)
    {
        return Arr::make(static::get_installed_payment_methods())->some(function ($method) use ($name) {
            return $method->name === $name && $method->type === PaymentGatewayType::MANUAL;
        });
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
	 * @return bool Returns true if the resolved gateway is an instance of
     *              FuturePaymentContract. Returns false if:
     *              - The name is empty,
     *              - The gateway cannot be resolved,
     *              - The gateway does not implement FuturePaymentContract.
	 */
    public static function is_support_future_payment(string $name)
    {
        if (empty($name)) {
            return false;
        }

        $payment_gateway = growfund_payment_gateway($name, false);

        if (empty($payment_gateway)) {
            return false;
        }

        if (!$payment_gateway instanceof FuturePaymentContract) {
            return false;
        }
        
        return true;
    }
}
