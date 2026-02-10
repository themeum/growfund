<?php

namespace Growfund\Hooks\Filters\Woocommerce;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\HookNames;
use Growfund\Constants\HookTypes;
use Growfund\Hooks\BaseHook;

/**
 * Woocommerce block theme checkout fields
 */
class BlockCustomCheckoutFields extends BaseHook
{
    public function get_name()
    {
        return HookNames::WC_BLOCK_CHECKOUT_FIELDS;
    }

    public function get_type()
    {
        return HookTypes::FILTER;
    }

    public function handle(...$args)
    {
        $fields = $args[0];

        if (!growfund_is_wc_checkout()) {
            return $fields;  
        }

        foreach (array_keys($fields) as $key) {
            $fields[$key]['country'] = [
				'required' => false,
				'class'   => ['growfund-remove-country'],
			];

            $fields[$key]['address'] = [
				'required' => false,
				'hidden'   => true,
			];

			$fields[$key]['address_1'] = [
				'required' => false,
				'hidden'   => true,
			];

            $fields[$key]['address_2'] = [
				'required' => false,
				'hidden'   => true,
			];

			$fields[$key]['postcode'] = [
				'required' => false,
				'hidden'   => true,
			];

			$fields[$key]['city'] = [
				'required' => false,
				'hidden'   => true,
			];

			$fields[$key]['company'] = [
				'required' => false,
				'hidden'   => true,
			];

			$fields[$key]['first_name'] = [
				'required' => false,
				'hidden'   => true,
			];

			$fields[$key]['last_name'] = [
				'required' => false,
				'hidden'   => true,
			];
	
			$fields[$key]['state'] = [
				'required' => false,
				'hidden'   => true,
			];
	
			$fields[$key]['phone'] = [
				'required' => false,
				'hidden'   => true,
			];

			$fields[$key]['email'] = [
				'required' => false,
				'hidden'   => true,
			];
        }

		return $fields;
    }
}
