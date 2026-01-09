<?php

namespace Growfund\Supports;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\DateTimeFormats;
use Growfund\Constants\PaymentEngine;
use Growfund\Constants\PledgeOption;
use Growfund\Constants\Status\DonationStatus;
use Growfund\Constants\Status\PaymentStatus;
use Growfund\Constants\Status\PledgeStatus;
use Growfund\Constants\Tables;
use Growfund\Constants\WP;
use Growfund\DTO\Pledge\PledgeRewardDTO;
use Growfund\Payments\Constants\PaymentGatewayType;
use Growfund\Payments\DTO\PaymentMethodDTO;
use Growfund\QueryBuilder;
use Growfund\Services\FundService;
use Growfund\Services\RewardService;
use WC_Order;

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

    public static function create_donation_from_order($order_id)
    {
        $order = wc_get_order($order_id);

        $contribution_info_dto = Woocommerce::get_custom_cart_info_from_session();

        if (empty($contribution_info_dto)) {
            return;
        }

        $order->update_meta_data(growfund_with_prefix('campaign_id'), $contribution_info_dto->campaign_id);

        foreach ($order->get_items() as $item_id => $item) {
            wc_update_order_item_meta($item_id, 'Campaign Name', $contribution_info_dto->campaign_name);
        }

        $order->save();

        static::create_donation($order);

        Woocommerce::unset_custom_cart_info_from_session();
    }

    public static function create_donation(WC_Order $order)
    {
        $donation_info = static::format_order($order);

        if (QueryBuilder::query()->table(Tables::DONATIONS)->where('transaction_id', $donation_info['transaction_id'])->first()) {
            return;
        }

        QueryBuilder::query()->table(Tables::DONATIONS)->create($donation_info);
    }

    public static function create_pledge_from_order($order_id)
    {
        $order = wc_get_order($order_id);

        $contribution_info_dto = Woocommerce::get_custom_cart_info_from_session();

        if (empty($contribution_info_dto)) {
            return;
        }

        $bonus_support_amount = $contribution_info_dto->bonus_support_amount ?? 0;

        $order->update_meta_data(growfund_with_prefix('campaign_id'), $contribution_info_dto->campaign_id);
        $order->update_meta_data(growfund_with_prefix('reward_id'), $contribution_info_dto->reward_id);
        $order->update_meta_data(growfund_with_prefix('bonus_support_amount'), $bonus_support_amount ?? 0);

        foreach ($order->get_items() as $item_id => $item) {
            wc_update_order_item_meta($item_id, 'Campaign Name', $contribution_info_dto->campaign_name);

            if ($bonus_support_amount) {
                wc_update_order_item_meta($item_id, 'Bonus Support Amount', $bonus_support_amount);
            }
        }

        $order->save();

        static::create_pledge($order);

        Woocommerce::unset_custom_cart_info_from_session();
    }

    public static function create_pledge(WC_Order $order)
    {
        $pledge_info = static::format_order($order, false);

        if (QueryBuilder::query()->table(Tables::PLEDGES)->where('transaction_id', $pledge_info['transaction_id'])->first()) {
            return;
        }

        QueryBuilder::query()->table(Tables::PLEDGES)->create($pledge_info);
    }

    public static function format_order(WC_Order $order, $is_donation = true)
    {
        foreach ($order->get_items() as $item) {
            $data = [];

            $campaign_id = $order->get_meta(growfund_with_prefix('campaign_id'));

            if ($is_donation) {
                $data['fund_id'] = static::get_fund_id($order);
                $data['is_anonymous'] = static::is_anonymous($order);
            } else {
                $reward_id = $order->get_meta(growfund_with_prefix('reward_id')) ?? null;
                $reward = static::get_reward_data($reward_id) ?? [];

                $data['reward_id'] = $reward_id;
                $data['bonus_support_amount'] = Money::prepare_for_storage($order->get_meta(growfund_with_prefix('bonus_support_amount')) ?? 0);
                $data['pledge_option'] = !empty($reward) ? PledgeOption::WITH_REWARDS : PledgeOption::WITHOUT_REWARDS;
                $data['reward_info'] = wp_json_encode($reward);
                $data['shipping_cost'] = (int) Money::prepare_for_storage($order->get_shipping_total());
            }

            $payment_method_dto = new PaymentMethodDTO();
            $payment_method_dto->name = $order->get_payment_method();
            $payment_method_dto->label = $order->get_payment_method_title();
            $payment_method_dto->type = PaymentGatewayType::ONLINE;

            $order_customer_id = $order->get_customer_id();

            return array_merge($data, [
                'campaign_id'                => $campaign_id,
                'uid'                        => growfund_uuid(),
                'user_id'                    => $order_customer_id ? $order_customer_id : null,
                'status'                     => static::contribution_status($order->get_status()),
                'amount'                     => (int) Money::prepare_for_storage($order->get_subtotal()),
                'recovery_fee'               => 0,
                'processing_fee'             => 0,
                'should_deduct_fee_recovery' => 0,
                'notes'                      => $order->get_customer_note(),
                'transaction_id'             => $order->get_id(),
                'payment_method'             => wp_json_encode($payment_method_dto->to_array()) ?? null,
                'payment_status'             => static::payment_status($order->get_status()),
                'payment_engine'             => PaymentEngine::WOOCOMMERCE,
                'is_manual'                  => 0,
                'user_info'                  => wp_json_encode(static::get_user_info($order)) ?? null,
                'created_at'                 => $order->get_date_created()->date(DateTimeFormats::DB_DATETIME),
                'created_by'                 => $order_customer_id ? $order_customer_id : 0,
                'updated_at'                 => $order->get_date_modified()->date(DateTimeFormats::DB_DATETIME),
                'updated_by'                 => $order_customer_id ? $order_customer_id : 0,
            ]);
        }
    }

    public static function get_reward_data($reward_id)
    {
        if (empty($reward_id)) {
            return [];
        }

        $reward_dto = (new RewardService())->get_by_id($reward_id);
        $reward_items = [];

        if (!empty($reward_dto->items)) {
            foreach ($reward_dto->items as $key => $item) {
                if (isset($item->quantity)) {
                    $item->quantity = (int) $item->quantity;
                }

                $item->image = $item->image['id'] ?? null;
                $reward_items[$key] = $item;
            }
        }

        $reward = PledgeRewardDTO::from_array([
            'id' => (string) $reward_dto->id,
            'title' => $reward_dto->title,
            'description' => $reward_dto->description,
            'image' => $reward_dto->image['id'] ?? null,
            'items' => $reward_items,
            'amount' => $reward_dto->amount ?? 0,
        ]);

        return $reward->to_array() ?? [];
    }

    public static function get_reward_id($campaign_id, $amount)
    {
        $postmeta_table = WP::POST_META_TABLE;

        $result = QueryBuilder::query()->table('posts as posts')
            ->select(['posts.ID as id'])
            ->join_raw(
                "{$postmeta_table} as amount_meta",
                "LEFT",
                sprintf("posts.ID = amount_meta.post_id AND amount_meta.meta_key = '%s' AND amount_meta.meta_value = %d", growfund_with_prefix('amount'), $amount)
            )
            ->where('posts.post_parent', $campaign_id)
            ->first();

        return $result->id ?? null;
    }

    public static function get_donation_amount($order)
    {
        return $order->get_meta(growfund_with_prefix('donation_amount')) ?? $order->get_meta("_wc_other/growfund/donation_amount") ?? (new FundService())->get_default_fund()->id;
    }

    public static function get_fund_id($order)
    {
        $id = $order->get_meta(growfund_with_prefix('fund_id'));

        if (empty($id)) {
            $id = $order->get_meta("_wc_other/growfund/fund_id");
        }

        if (empty($id)) {
            $id = (new FundService())->get_default_fund()->id;
        }

        return $id;
    }

    public static function is_anonymous($order)
    {
        $is_anonymous = $order->get_meta(growfund_with_prefix('is_anonymous'));

        if (empty($is_anonymous)) {
            $is_anonymous = $order->get_meta("_wc_other/growfund/is_anonymous");
        }

        return $is_anonymous ?? 0;
    }

    public static function get_user_info(WC_Order $order): array
    {
        return [
            'id'      => (string) $order->get_customer_id(),
            'first_name' => $order->get_billing_first_name(),
            'last_name'  => $order->get_billing_last_name(),
            'email'   => $order->get_billing_email(),
            'phone'   => $order->get_billing_phone(),
            'image'   => null,
            'shipping_address' => [
                'address'   => $order->get_shipping_address_1(),
                'address_2' => $order->get_shipping_address_2(),
                'city'      => $order->get_shipping_city(),
                'state'     => $order->get_shipping_state(),
                'zip_code'  => $order->get_shipping_postcode(),
                'country'   => $order->get_shipping_country(),
            ],
            'billing_address' => [
                'address'   => $order->get_billing_address_1(),
                'address_2' => $order->get_billing_address_2(),
                'city'      => $order->get_billing_city(),
                'state'     => $order->get_billing_state(),
                'zip_code'  => $order->get_billing_postcode(),
                'country'   => $order->get_billing_country(),
            ],
            'is_billing_address_same' => $order->get_billing_address_1() === $order->get_shipping_address_1()
                && $order->get_billing_address_2() === $order->get_shipping_address_2()
                && $order->get_billing_city() === $order->get_shipping_city()
                && $order->get_billing_state() === $order->get_shipping_state()
                && $order->get_billing_postcode() === $order->get_shipping_postcode()
                && $order->get_billing_country() === $order->get_shipping_country(),

            'created_at' => $order->get_customer_id() ? get_userdata($order->get_customer_id())->user_registered : $order->get_date_created()->getTimestamp(),
        ];
    }
}
