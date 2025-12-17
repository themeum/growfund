<?php

namespace Growfund\Services\Migration;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\DateTimeFormats;
use Growfund\Constants\PaymentEngine;
use Growfund\Constants\PledgeOption;
use Growfund\Constants\Tables;
use Growfund\Constants\UserTypes\Backer;
use Growfund\Constants\WC;
use Growfund\Constants\WP;
use Growfund\DTO\Pledge\PledgeRewardDTO;
use Growfund\Payments\Constants\PaymentGatewayType;
use Growfund\Payments\DTO\PaymentMethodDTO;
use Growfund\QueryBuilder;
use Growfund\Services\BackerService;
use Growfund\Services\RewardService;
use Growfund\Supports\Money;
use Growfund\Supports\WoocommerceToNative;
use Exception;
use Growfund\DTO\Backer\CreateBackerDTO;
use Growfund\DTO\Backer\UpdateBackerDTO;
use Growfund\DTO\Migration\MigrationResponseDTO;
use WC_Order;

class PledgeMigrationService
{
    const BATCH_SIZE = 10;
    const OFFSET_KEY = 'growfund_pledge_migration_offset';
    const TOTAL_KEY = 'growfund_pledge_migration_total';

    public function migrate()
    {
        $offset = $this->get_offset(0);
        
        QueryBuilder::begin_transaction();

        $total = $this->get_total();
        $orders = $this->get_orders($offset);
        $pledges = $this->update_backers_and_format_pledges($orders);

        $response = new MigrationResponseDTO();
        $response->total = $total;
        $response->completed = $offset;

        if (empty($orders)) {
            QueryBuilder::rollback();
            return $response;
        }

        try {
            if (!empty($pledges)) {
                $this->migrate_pledges($pledges);
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
     * @param int $offset
     * @return \WC_Order[]
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
     * @param WC_Order[] $orders
     */
    protected function update_backers_and_format_pledges($orders)
    {
        $pledges = [];

        foreach ($orders as $order) {
            $pledge = $this->format_order_and_update_backer($order);

            if (!empty($pledge)) {
                $pledges[] = $pledge;
            }
        }

        return $pledges;
    }

    protected function migrate_pledges($pledges)
    {
        QueryBuilder::query()->table(Tables::PLEDGES)->insert($pledges);
        $this->link_to_growfund_product($pledges);

        return true;
    }

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

    protected function format_order_and_update_backer(WC_Order $order)
    {
        foreach ($order->get_items() as $item) {
            $product = $item->get_product();

            if ((!$product || $product->get_type() !== 'crowdfunding') && (int) $order->get_meta('is_crowdfunding_order') !== 1) {
                continue;
            }

            $campaign_id = $product->get_id();

            $reward = get_post_meta($order->get_id(), 'wpneo_selected_reward', true);
            $reward = !empty($reward) && growfund_is_valid_json($reward) ? json_decode($reward, true) : [];
            $reward_id = !empty($reward) ? $this->get_reward_id($campaign_id, $reward['wpneo_rewards_pladge_amount']) : null;

            if ($reward_id) {
                $reward_dto = (new RewardService())->get_by_id($reward_id);
                $reward_items = [];

                if (!empty($reward_dto->items)) {
                    foreach ($reward_dto->items as $key => $item) {
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

                $reward = $reward->to_array() ?? [];
            }

            $payment_method_dto = new PaymentMethodDTO();
            $payment_method_dto->name = $order->get_payment_method();
            $payment_method_dto->label = $order->get_payment_method_title();
            $payment_method_dto->type = in_array($order->get_payment_method(), ['bacs', 'cheque', 'cod'], true) 
                ? PaymentGatewayType::MANUAL 
                : PaymentGatewayType::ONLINE;

            $user_info = $this->update_and_get_backer_info($order);

            return [
                'uid'                        => growfund_uuid(),
                'campaign_id'                => $campaign_id,
                'user_id'                    => !empty($user_info['id']) ? $user_info['id'] : null,
                'email'                      => !empty($user_info['email']) ? $user_info['email'] : null,
                'status'                     => WoocommerceToNative::pledge_status($order->get_status()),
                'pledge_option'              => !empty($reward) ? PledgeOption::WITH_REWARDS : PledgeOption::WITHOUT_REWARDS,
                'reward_id'                  => $reward_id,
                'amount'                     => (int) Money::prepare_for_storage($order->get_total()),
                'bonus_support_amount'       => 0,
                'shipping_cost'              => (int) Money::prepare_for_storage($order->get_shipping_total()),
                'processing_fee'             => 0,
                'notes'                      => $order->get_customer_note(),
                'transaction_id'             => $order->get_id(),
                'payment_method'             => wp_json_encode($payment_method_dto->to_array()),
                'payment_status'             => WoocommerceToNative::payment_status($order->get_status()),
                'payment_engine'             => PaymentEngine::WOOCOMMERCE,
                'is_manual'                  => 0,
                'reward_info'                => wp_json_encode($reward),
                'user_info'                  => wp_json_encode($user_info),
                'created_at'                 => $order->get_date_created()->date(DateTimeFormats::DB_DATETIME),
                'created_by'                 => $order->get_customer_id() ? $order->get_customer_id() : 0, 
                'updated_at'                 => $order->get_date_modified()->date(DateTimeFormats::DB_DATETIME),
                'updated_by'                 => $order->get_customer_id() ? $order->get_customer_id() : 0,
            ];
        }

        return [];
    }

    public function get_reward_id($campaign_id, $amount)
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

    protected function update_and_get_backer_info(WC_Order $order)
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
            if (empty($data['email'])) {
                return $data;
            }
            
            $data['id'] = $this->create_backer(CreateBackerDTO::from_array($data));

            return $data;
        }

        $this->update_backer_info($user->ID, UpdateBackerDTO::from_array($data));

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

        $user->set_role(Backer::ROLE);
    }

    protected function update_backer_info(int $id, UpdateBackerDTO $dto)
    {
        $user = growfund_user($id);

        if ($user->is_admin()) {
            return;
        }

        $backer_service = new BackerService();

        return $backer_service->update($id, $dto);
    }
    
    protected function create_backer(CreateBackerDTO $dto)
    {
        $backer_service = new BackerService();

        return $backer_service->store($dto);
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
