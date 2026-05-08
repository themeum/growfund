<?php

namespace Growfund\Hooks\Actions\Woocommerce;

defined( 'ABSPATH' ) || exit;

use Exception;
use Growfund\Constants\HookNames;
use Growfund\Constants\HookTypes;
use Growfund\Hooks\BaseHook;
use Growfund\Services\DonationService;
use Growfund\Services\PledgeService;
use Growfund\Supports\Woocommerce;

class BeforeCalculateTotal extends BaseHook
{
    public function get_name()
    {
        return HookNames::WC_BEFORE_CALCULATE_TOTAL;
    }

    public function get_type()
    {
        return HookTypes::ACTION;
    }

    public function handle(...$args)
    {
        if (is_admin() && !defined('DOING_AJAX')) {
			return;
		}
    
        /** @var \WC_Cart */
        $cart = $args[0];

        if (!Woocommerce::has_growfund_product_in_cart($cart ?? null)) {
            return;
        }

        foreach ($cart->get_cart() as $cart_item) {
            /** @var \WC_Product $product */
            $product = $cart_item['data'];

            if (! Woocommerce::is_growfund_product($product)) {
                continue;
            }

            $contribution_id = $cart_item[growfund_with_prefix('contribution_id')] ?? null;
            
            if (empty($contribution_id)) {
                continue;
            }

            if (growfund_app()->is_donation_mode()) {
                try {
                    $donation = (new DonationService())->get_by_id($contribution_id)->get_values();
                } catch (Exception $_) {
                    continue;
                }
				$product->set_price($donation->amount);
				$product->set_sale_price($donation->amount);
                $product->set_regular_price($donation->amount);

				$customer = WC()->customer;

				$customer->set_billing_first_name($donation->donor->first_name);
				$customer->set_billing_last_name($donation->donor->last_name);
				$customer->set_billing_email($donation->donor->email);
				$customer->set_billing_phone($donation->donor->phone);
				$customer->set_billing_address_1($donation->donor->billing_address['address'] ?? '');
				$customer->set_billing_address_2($donation->donor->billing_address['address_2'] ?? '');
				$customer->set_billing_city($donation->donor->billing_address['city'] ?? '');
				$customer->set_billing_state($donation->donor->billing_address['state'] ?? '');
				$customer->set_billing_country($donation->donor->billing_address['country'] ?? '');
				$customer->set_billing_postcode($donation->donor->billing_address['zip_code'] ?? '');

                break;
            }

            try {
                $pledge = (new PledgeService())->get_by_id($contribution_id)->get_values();
            } catch (Exception $_) {
                continue;
            }
            
            $product->set_price($pledge->payment->amount);
            $product->set_sale_price($pledge->payment->amount);
            $product->set_regular_price($pledge->payment->amount);
            $cart->add_fee(__('Bonus Support Amount', 'growfund'), $pledge->payment->bonus_support_amount);
            $cart->add_fee(__('Shipping', 'growfund'), $pledge->payment->shipping_cost);

            $customer = WC()->customer;

            $customer->set_billing_first_name($pledge->backer->first_name);
            $customer->set_billing_last_name($pledge->backer->last_name);
            $customer->set_billing_email($pledge->backer->email);
            $customer->set_billing_phone($pledge->backer->phone);
            $customer->set_billing_address_1($pledge->backer->billing_address['address'] ?? '');
            $customer->set_billing_address_2($pledge->backer->billing_address['address_2'] ?? '');
            $customer->set_billing_city($pledge->backer->billing_address['city'] ?? '');
            $customer->set_billing_state($pledge->backer->billing_address['state'] ?? '');
            $customer->set_billing_country($pledge->backer->billing_address['country'] ?? '');
            $customer->set_billing_postcode($pledge->backer->billing_address['zip_code'] ?? '');
            break;
        }
    }
}
