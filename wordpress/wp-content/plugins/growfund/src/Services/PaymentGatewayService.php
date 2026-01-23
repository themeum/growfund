<?php 

namespace Growfund\Services;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\AppConfigKeys;
use Growfund\Core\AppSettings;
use Growfund\Supports\Arr;
use Growfund\Supports\Option;

class PaymentGatewayService {
    /**
     * Store payment gateway information.
     * 
     * @param string $name The name of the payment gateway.
     * @param array $payload The payment gateway information.
     * 
     * @return void
     */
    public function store_gateway_info(string $name, array $payload) {
        $payments = growfund_settings(AppSettings::PAYMENT)->refresh()->get('payments', []);
        $payments = Arr::make($payments);
        $payment = $payments->find(function ($item) use ($name) {
            return $item['name'] === $name;
        });

        if (!empty($payload['config']) && !empty($payload['config']['logo']) && str_starts_with($payload['config']['logo'], '/')) {
            $payload['config']['logo'] = growfund_image_url($payload['config']['logo']);
        }

        if (empty($payment)) {
            $payments->push($payload);
        } else {
            $payments = $payments->map(function ($item) use ($name, $payload) {
                if ($item['name'] === $name) {
                    return array_merge($item, $payload);
                }
                return $item;
            });
        }

        $existing_payment_settings = growfund_settings(AppSettings::PAYMENT)->refresh()->get() ?? [];
        $updated_payment_settings = array_merge(
            $existing_payment_settings,
            [
                'payments' => $payments->toArray(),
            ]
        );

        Option::update(AppConfigKeys::PAYMENT, $updated_payment_settings);
    }

    public function remove_gateway_info(string $name) {
        $payments = growfund_settings(AppSettings::PAYMENT)->refresh()->get('payments', []);
        $payments = Arr::make($payments)
            ->filter(function ($item) use ($name) {
                return $item['name'] !== $name;
            });

        $existing_payment_settings = growfund_settings(AppSettings::PAYMENT)->refresh()->get() ?? [];
        $updated_payment_settings = array_merge(
            $existing_payment_settings,
            [
                'payments' => $payments->toArray(),
            ]
        );

        Option::update(AppConfigKeys::PAYMENT, $updated_payment_settings);
    }
}
