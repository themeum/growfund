<?php

namespace Growfund\Services;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\Tables;
use Growfund\DTO\Fund\FundDTO;
use Growfund\Http\Response;
use Growfund\QueryBuilder;
use Growfund\Services\Analytics\DonationAnalyticService;
use Growfund\Supports\Arr;
use Exception;
use Growfund\Constants\HookNames;

/**
 * FundService class
 * @since 1.0.0
 */
class FundService
{
    /**
     * The DonationService instance.
     * @var 
     */
    protected $donation_service;

    protected $donation_analytic_service;

    /**
     * Initialize the service with the DonationService instance.
     */
    public function __construct()
    {
        $this->donation_service = new DonationService();
        $this->donation_analytic_service = new DonationAnalyticService();
    }

    /**
     * Get fund by ID
     * 
     * @param int $id
     * 
     * @return FundDTO
     */
    public function get_by_id(int $id)
    {
        $fund = QueryBuilder::query()->table(Tables::FUNDS)->select(['ID', 'title', 'description', 'status', 'created_at', 'created_by', 'updated_at', 'updated_by'])->find($id);

        if (empty($fund)) {
            throw new Exception(esc_html__('Fund not found', 'growfund'), (int) Response::NOT_FOUND);
        }

        $fund->id = (string) $id;

        return FundDTO::from_array((array) $fund);
    }

    /**
     * Get fund by IDs
     * 
     * @param int $id
     * 
     * @return FundDTO[]
     * @throws Exception
     */
    public function get_by_ids(array $ids)
    {
        $funds = QueryBuilder::query()->table(Tables::FUNDS)->select(['ID as id', 'title', 'description', 'status', 'created_at', 'created_by', 'updated_at', 'updated_by'])->where_in('ID', $ids)->get();

        if (empty($funds)) {
            throw new Exception(esc_html__('No funds found', 'growfund'), (int) Response::NOT_FOUND);
        }

        return Arr::make($funds)->map(function ($fund) {
            return FundDTO::from_array((array) $fund);
        })->toArray();
    }

    /**
     * Get fund by ID
     * 
     * @return FundDTO
     */
    public function get_default_fund()
    {
        $fund = QueryBuilder::query()->table(Tables::FUNDS)
            ->select(['ID', 'title', 'description', 'status', 'created_at', 'created_by', 'updated_at', 'updated_by'])
            ->where('is_default', 1)
            ->first();

        if (empty($fund)) {
            throw new Exception(esc_html__('Fund not found', 'growfund'), (int) Response::NOT_FOUND);
        }

        $fund->id = (string) $fund->ID;

        return FundDTO::from_array((array) $fund);
    }

    /**
     * Get list of all funds without pagination
     *
     * @return array
     */
    public function all() {
        return apply_filters(HookNames::GROWFUND_FUND_LIST_FILTER, []); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
    }
}
