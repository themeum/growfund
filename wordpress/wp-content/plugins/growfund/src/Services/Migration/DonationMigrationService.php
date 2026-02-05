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
use Growfund\DTO\Migration\MigrationResponseDTO;
use Growfund\Services\FundService;
use Growfund\Supports\User as UserSupport;
use Growfund\Supports\UserMeta;
use WC_Order;
use WP_User;

class DonationMigrationService
{
    const BATCH_SIZE = 10;
    const OFFSET_KEY = 'growfund_donation_migration_offset';
    const TOTAL_KEY = 'growfund_donation_migration_total';

    private $statuses = ['wc-completed', 'wc-processing', 'wc-on-hold', 'wc-pending', 'wc-cancelled', 'wc-refunded', 'wc-failed'];

    public function migrate()
    {
        wc_set_time_limit(2000);
        add_filter('admin_memory_limit', function () {
            return '512M';
        });
        wp_raise_memory_limit();
        
        $offset = $this->get_offset(0);
        
        QueryBuilder::begin_transaction();

        $total = $this->get_total();
        $orders = $this->get_orders($offset);

        $response = new MigrationResponseDTO();
        $response->total = $total;
        $response->completed = $offset;

        try {
			if (empty($orders)) {
				QueryBuilder::rollback();
				return $response;
			}

            $donations = $this->update_donors_and_format_donations($orders);
        
            if (!empty($donations)) {
                $this->migrate_donations($donations);
            }
            
            $offset = $offset + count($orders);
            $this->set_offset($offset);
            
            $response->total = $total;
            $response->completed = $offset;

            QueryBuilder::commit();
        } catch (Exception $e) {
            growfund_error_log($e->getMessage());
            QueryBuilder::rollback();
        }

        return $response;
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
            'status' => $this->statuses,
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

			if (!$product || ($product->get_type() !== 'crowdfunding' && (int) $order->get_meta('is_crowdfunding_order') !== 1)) {
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
                'user_id'                    => !empty($user_info['id']) ? $user_info['id'] : null,
                'email'                      => !empty($user_info['email']) ? $user_info['email'] : null,
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
                'created_by'                 => $order->get_customer_id() ? $order->get_customer_id() : 0,
                'updated_at'                 => $order->get_date_modified()->date(DateTimeFormats::DB_DATETIME),
                'updated_by'                 => $order->get_customer_id() ? $order->get_customer_id() : 0,
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
            return $data;
        }

        $this->ensure_user_role($user);
        UserMeta::update($user->ID, 'is_billing_address_same', $data['is_billing_address_same']);
        UserMeta::update($user->ID, 'billing_address', $data['billing_address']);
        UserMeta::update($user->ID, 'shipping_address', $data['shipping_address']);
        UserMeta::update($user->ID, 'phone', $data['phone']);

        return $data;
    }

    /**
     * Ensure user has the donor role
     * 
     * @param WP_User|null $user
     * @return void
     */
    protected function ensure_user_role($user)
    {
        if (empty($user) || UserSupport::is_admin($user) || UserSupport::is_fundraiser($user)) {
            return;
        }

        $user->add_role(Donor::ROLE);
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
            $total = 0;

            foreach ($this->statuses as $status) {
                $total += wc_orders_count($status);
            }
                
            set_transient(static::TOTAL_KEY, $total, time() + 24 * 60 * 60);
        }

        return $total;
    }
}
