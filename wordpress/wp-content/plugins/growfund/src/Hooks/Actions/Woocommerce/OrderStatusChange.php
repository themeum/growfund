<?php

namespace Growfund\Hooks\Actions\Woocommerce;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\HookNames;
use Growfund\Constants\HookTypes;
use Growfund\Constants\Tables;
use Growfund\Hooks\BaseHook;
use Growfund\QueryBuilder;
use Growfund\Supports\Date;
use Growfund\Supports\WoocommerceToNative;

class OrderStatusChange extends BaseHook
{
    public function get_name()
    {
        return HookNames::WC_ORDER_STATUS_CHANGED;
    }

    public function get_type()
    {
        return HookTypes::ACTION;
    }

    public function get_args_count()
    {
        return 4;
    }

    public function handle(...$args)
    {
        list($order_id, $previous_status, $next_status, $order) = $args;

        $this->sync_contribution_status($order_id, $next_status);
    }

    protected function sync_contribution_status($order_id, $new_status)
    {
        $table = growfund_app()->is_donation_mode() ? Tables::DONATIONS : Tables::PLEDGES;

        QueryBuilder::query()->table($table)
            ->where('transaction_id', $order_id)
            ->update([
                'status' => WoocommerceToNative::contribution_status($new_status),
                'payment_status' => WoocommerceToNative::payment_status($new_status),
                'updated_at' => Date::current_sql_safe()
            ]);
    }
}
