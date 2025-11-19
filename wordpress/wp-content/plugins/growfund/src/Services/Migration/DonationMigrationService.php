<?php

namespace Growfund\Services\Migration;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\DateTimeFormats;
use Growfund\Constants\PaymentEngine;
use Growfund\Constants\Tables;
use Growfund\Constants\WC;
use Growfund\Payments\Constants\PaymentGatewayType;
use Growfund\Payments\DTO\PaymentMethodDTO;
use Growfund\QueryBuilder;
use Growfund\Supports\Money;
use Growfund\Supports\WoocommerceToNative;
use Exception;
use Growfund\Constants\UserTypes\Donor;
use Growfund\DTO\Donor\CreateDonorDTO;
use Growfund\DTO\Donor\UpdateDonorDTO;
use Growfund\DTO\Migration\MigrationResponseDTO;
use Growfund\Services\DonorService;
use Growfund\Services\FundService;
use WC_Order;

class DonationMigrationService
{
    const BATCH_SIZE = 10;
    const OFFSET_KEY = 'growfund_donation_migration_offset';
    const TOTAL_KEY = 'growfund_donation_migration_total';

    public function migrate()
    {
        $offset = $this->get_offset(0);
        
        QueryBuilder::begin_transaction();

        $total = $this->get_total();
        $orders = $this->get_orders($offset);
        $donations = $this->update_donors_and_format_donations($orders);

        $response = new MigrationResponseDTO();
        $response->total = $total;
        $response->completed = $offset;

        if (empty($orders)) {
            QueryBuilder::rollback();
            return $response;
        }

        try {
            if (!empty($donations)) {
                $this->migrate_donations($donations);
            }
            
            $offset = $offset + count($orders);
            $this->set_offset($offset);
            
            $response->total = $total;
            $response->completed = $offset;

            QueryBuilder::commit();

            return $response;
        } catch (Exception $e) {
            error_log($e->getMessage()); // phpcs:ignore
            QueryBuilder::rollback();

            return $response;
        }
    }

    /**
     * Get a list of orders with the given offset and status.
     * 
     * @param int $offset
     * @return WC_Order[]
     */
    protected function get_orders($offset)
    {
        $orders = wc_get_orders([
            'limit' => static::BATCH_SIZE,
            'offset' => $offset,
            'status' => ['wc-completed', 'wc-processing', 'wc-on-hold', 'wc-pending', 'wc-cancelled', 'wc-refunded', 'wc-failed'],
            'orderby' => 'date',
            'order' => 'ASC',
        ]);

        return $orders ?? [];
    }

    /**
     * Loop through the given orders and format them as donations
     * and update the associated backers.
     * 
     * @param WC_Order[] $orders
     * @return array
     */
    protected function update_donors_and_format_donations($orders)
    {
        $donations = [];

        foreach ($orders as $order) {
            $donation = $this->format_order_and_update_donor($order);

            if (!empty($donation)) {
                $donations[] = $donation;
            }
        }

        return $donations;
    }

	/**
	 * Insert the given donations into the pledges table and link them to the
	 * corresponding GF product.
	 *
	 * @param array $donations
	 * @return bool
	 */
    protected function migrate_donations($donations)
    {
        QueryBuilder::query()->table(Tables::DONATIONS)->insert($donations);
        $this->link_to_growfund_product($donations);

        return true;
    }

	/**
	 * Update the product id of the given orders to the Growfund WC product id.
	 *
	 * @param array $orders
	 * @return bool
	 */
    protected function link_to_growfund_product($orders)
    {
        $ids = array_map(function ($order) {
            return $order['campaign_id'];
        }, $orders);

        $order_item_meta_table = WC::ORDER_ITEM_META;

        QueryBuilder::query()->table($order_item_meta_table)
            ->where('meta_key', '_product_id')
            ->where_in('meta_value', $ids)->update([
                'meta_value' => growfund_wc_product_id() // phpcs:ignore
            ]);

        return true;
    }

    /**
     * Format the given order and update the associated backer.
     *
     * @param WC_Order $order
     * @return array
     */
    protected function format_order_and_update_donor(WC_Order $order)
    {
        foreach ($order->get_items() as $item) {
            $product = $item->get_product();

            if ((!$product || $product->get_type() !== 'crowdfunding') && (int) $order->get_meta('is_crowdfunding_order') !== 1) {
                continue;
            }

            $campaign_id = $product->get_id();

            $payment_method_dto = new PaymentMethodDTO();
            $payment_method_dto->name = $order->get_payment_method();
            $payment_method_dto->label = $order->get_payment_method_title();
            $payment_method_dto->type = in_array($order->get_payment_method(), ['bacs', 'cheque', 'cod'], true) 
                ? PaymentGatewayType::MANUAL 
                : PaymentGatewayType::ONLINE;

            $user_info = $this->update_and_get_donor_info($order);

            return [
                'uid'                        => growfund_uuid(),
                'campaign_id'                => $campaign_id,
                'user_id'                    => $user_info['id'] ?: null, // phpcs:ignore
                'fund_id'                    => (new FundService())->get_default_fund()->id,
                'amount'                     => (int) Money::prepare_for_storage($order->get_total()),
                'recovery_fee'               => 0,
                'processing_fee'             => 0,
                'should_deduct_fee_recovery' => false,
                'status'                     => WoocommerceToNative::donation_status($order->get_status()),
                'notes'                      => $order->get_customer_note(),
                'transaction_id'             => $order->get_id(),
                'payment_engine'             => PaymentEngine::WOOCOMMERCE,
                'payment_method'             => wp_json_encode($payment_method_dto->to_array()),
                'payment_status'             => WoocommerceToNative::payment_status($order->get_status()),
                'is_manual'                  => 0,
                'is_anonymous'               => empty($user_info['id']) ? 1 : 0,
                'user_info'                  => wp_json_encode($user_info),
                'created_at'                 => $order->get_date_created()->date(DateTimeFormats::DB_DATETIME),
                'created_by'                 => $order->get_customer_id() ?: 0, // phpcs:ignore
                'updated_at'                 => $order->get_date_modified()->date(DateTimeFormats::DB_DATETIME),
                'updated_by'                 => $order->get_customer_id() ?: 0, // phpcs:ignore
            ];
        }

        return [];
    }

    /**
     * Retrieves and updates the user information for a given order.
     *
     * @param WC_Order $order The order object.
     * @return array The updated user information.
     */
    protected function update_and_get_donor_info(WC_Order $order)
    {
        $this->ensure_user_role($order->get_customer_id());

        $data = [
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
        ];

        $user = get_user($order->get_customer_id());

        if (empty($user)) {
            $user = get_user_by('email', $order->get_billing_email());
        }

        if (empty($user)) {
            $data['id'] = $this->create_donor(CreateDonorDTO::from_array($data));

            return $data;
        }

        $this->update_donor_info($user->ID, UpdateDonorDTO::from_array($data));

        return $data;
    }

    protected function ensure_user_role($user_id)
    {
        $user = growfund_user($user_id);

        if ($user->is_admin()) {
            return;
        }

        if ($user->is_fundraiser()) {
            return;
        }

        $user->set_role(Donor::ROLE);
    }

    protected function update_donor_info(int $id, UpdateDonorDTO $dto)
    {
        $user = growfund_user($id);

        if ($user->is_admin()) {
            return;
        }

        $donor_service = new DonorService();

        return $donor_service->update($id, $dto);
    }
    
    protected function create_donor(CreateDonorDTO $dto)
    {
        $donor_service = new DonorService();

        return $donor_service->store($dto);
    }

    protected function get_offset(int $default = 0)
    {
        return (int) get_transient(static::OFFSET_KEY) ?? $default;
    }
    
    protected function set_offset(int $offset)
    {
        set_transient(static::OFFSET_KEY, $offset, time() + 24 * 60 * 60);
    }

    protected function get_total()
    {
        $total = (int) get_transient(static::TOTAL_KEY);

        if (!$total) {
            $total = QueryBuilder::query()->table(WC::ORDERS)
                ->where_in('status', ['wc-completed', 'wc-processing', 'wc-on-hold', 'wc-pending', 'wc-cancelled', 'wc-refunded', 'wc-failed'])
                ->count();
                
            set_transient(static::TOTAL_KEY, $total, time() + 24 * 60 * 60);
        }

        return $total;
    }
}
