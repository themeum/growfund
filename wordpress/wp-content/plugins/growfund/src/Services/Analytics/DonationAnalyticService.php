<?php

namespace Growfund\Services\Analytics;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\DateTimeFormats;
use Growfund\Constants\Status\CampaignStatus;
use Growfund\Constants\Status\DonationStatus;
use Growfund\Constants\Status\FundStatus;
use Growfund\Constants\Tables;
use Growfund\Constants\WP;
use Growfund\DTO\Campaign\CampaignAnalyticDTO;
use Growfund\DTO\Donor\DonorDTO;
use Growfund\DTO\Donation\DonationFilterParamsDTO;
use Growfund\DTO\RevenueChartDTO;
use Growfund\QueryBuilder;
use Growfund\Services\DonationService;
use Growfund\Supports\Arr;
use Growfund\Supports\MediaAttachment;
use Growfund\Supports\Money;
use Growfund\Supports\Utils;
use DateTime;
use Growfund\Constants\UserTypes\Collaborator;
use Growfund\Constants\UserTypes\Fundraiser;
use Growfund\DTO\Donation\DonationDonorDTO;

class DonationAnalyticService
{
    /**
     * @var DonationService
     */
    protected $donation_service;

    public function __construct()
    {
        $this->donation_service = new DonationService();
    }

    public function get_metrics(DateTime $start_date, DateTime $end_date, $campaign_id = null)
    {
        $interval = $start_date->diff($end_date);

        $previous_end = clone $start_date;
        $previous_end->modify('-1 day');

        $previous_start = clone $previous_end;
        $previous_start->sub($interval);

        $start_date = $start_date->format('Y-m-d');
        $end_date = $end_date->format('Y-m-d');
        $previous_start = $previous_start->format('Y-m-d');
        $previous_end = $previous_end->format('Y-m-d');

        // Current period values
        $total_fund_raised = (int) $this->donation_service->get_total_donated_amount($campaign_id, $start_date, $end_date);
        $total_net_amount = (int) $this->donation_service->get_total_net_amount($campaign_id, $start_date, $end_date);
        $total_donors_count = (int) $this->donation_service->get_total_donors_count_for_campaign($campaign_id, $start_date, $end_date);
        $avg_donation =  (int) $this->donation_service->get_total_average_amount($campaign_id, $start_date, $end_date);

        // Previous period values
        $prev_fund_raised = (int) $this->donation_service->get_total_donated_amount($campaign_id, $previous_start, $previous_end);
        $prev_net_amount = (int) $this->donation_service->get_total_net_amount($campaign_id, $previous_start, $previous_end);
        $prev_donors_count = (int) $this->donation_service->get_total_donors_count_for_campaign($campaign_id, $previous_start, $previous_end);
        $prev_avg_donation =  (int) $this->donation_service->get_total_average_amount($campaign_id, $previous_start, $previous_end);

        return [
            'total_donation' => [
                'amount' => Money::prepare_for_display($total_fund_raised),
                'growth' => Utils::calculate_growth_in_percent($total_fund_raised, $prev_fund_raised),
            ],
            'net_donation' => [
                'amount' => Money::prepare_for_display($total_net_amount),
                'growth' => Utils::calculate_growth_in_percent($total_net_amount, $prev_net_amount),
            ],
            'average_donation' => [
                'amount' => Money::prepare_for_display($avg_donation),
                'growth' => Utils::calculate_growth_in_percent($avg_donation, $prev_avg_donation),
            ],
            'total_donors' => [
                'amount' => $total_donors_count,
                'growth' => Utils::calculate_growth_in_percent($total_donors_count, $prev_donors_count),
            ]
        ];
    }

    /**
     * Retrieves revenue chart data for a specific fund, aggregated over a determined interval.
     *
     * This function queries the donations table to fetch donation amounts associated with the specified fund ID.
     * It then aggregates the donation amounts over defined time intervals (daily, weekly, or monthly) based on the
     * date range difference. The results are returned as an array of RevenueChartDTO objects, with each entry
     * containing the date label and the corresponding total revenue.
     *
     * @param DateTime $start_date The start date for the data range in 'Y-m-d' format. If null, defaults to 30 days before the end date.
     * @param DateTime $end_date The end date for the data range in 'Y-m-d' format. If null, defaults to the current date.
     * @param int $campaign_id The ID of the campaign for which to retrieve revenue data.
     * @param int $fund_id The ID of the fund for which to retrieve revenue data.
     * 
     * @return RevenueChartDTO[] An array of objects containing the date label and total revenue for each interval.
     */
    public function get_revenue_chart_data(DateTime $start_date, DateTime $end_date, $campaign_id = null, $fund_id = null)
    {
        $donations_table = Tables::DONATIONS;
        $query =  QueryBuilder::query()->table($donations_table . ' as donations')
            ->select([
                "DATE(created_at) as created_at",
                "SUM(amount) AS total_contributions",
            ]);

        if (growfund_user()->has_active_role(Fundraiser::ROLE)) {
            $campaign_ids = growfund_campaign_ids_by_fundraiser();
            $query->where_in('campaign_id', $campaign_ids);
        }

        if (growfund_user()->has_active_role(Collaborator::ROLE)) {
            $campaign_ids = growfund_campaign_ids_by_collaborator();
            $query->where_in('campaign_id', $campaign_ids);
        }

        if (!empty($campaign_id)) {
            $query->where('campaign_id', $campaign_id);
        }

        if (!empty($fund_id)) {
            $query->where('fund_id', $fund_id);
        }

        $raw_results = $query->where('status', '=', DonationStatus::COMPLETED)
            ->where_between(
                'DATE(created_at)',
                $start_date->format(DateTimeFormats::DB_DATE),
                $end_date->format(DateTimeFormats::DB_DATE)
            )
            ->group_by('DATE(created_at)')
            ->order_by('DATE(created_at)', 'ASC')
            ->get();


        $revenue_service = new RevenueService($start_date, $end_date);

        return $revenue_service->prepare_chart_data($raw_results);
    }
    
    /**
     * Retrieves donor over time chart data.
     *
     * @param DateTime $start_date The start date for the data range in 'Y-m-d' format. If null, defaults to 30 days before the end date.
     * @param DateTime $end_date The end date for the data range in 'Y-m-d' format. If null, defaults to the current date.
     *
     * @return array An array of objects containing the date label, first time total, and recurring total for each interval.
     */
    public function get_donor_over_time_chart_data(DateTime $start_date, DateTime $end_date)
    {
        $donations_table = QueryBuilder::prefix(Tables::DONATIONS);

        $fundraiser_where = '';

        if (growfund_user()->has_active_role(Fundraiser::ROLE)) {
            $campaign_ids = implode(',', array_map('intval', growfund_campaign_ids_by_fundraiser()));

            if (!empty($campaign_ids)) {
                $fundraiser_where = "AND d.campaign_id IN ($campaign_ids)";
            }
        }

        if (growfund_user()->has_active_role(Collaborator::ROLE)) {
            $campaign_ids = implode(',', array_map('intval', growfund_campaign_ids_by_collaborator()));

            if (!empty($campaign_ids)) {
                $fundraiser_where = "AND d.campaign_id IN ($campaign_ids)";
            }
        }

        $start = $start_date->format(DateTimeFormats::DB_DATE);
        $end = $end_date->format(DateTimeFormats::DB_DATE);

        $sql = "SELECT DATE(d.created_at) AS contribution_date,
                SUM(CASE WHEN d.id = fd.first_id THEN 1 ELSE 0 END) AS first_time_total,
                SUM(CASE WHEN d.id != fd.first_id THEN 1 ELSE 0 END) AS recurring_total
            FROM {$donations_table} AS d
            INNER JOIN (
                SELECT user_id, MIN(id) AS first_id
                FROM {$donations_table}
                WHERE status = 'completed'
                GROUP BY user_id
            ) AS fd ON d.user_id = fd.user_id
            WHERE d.status = 'completed'
            {$fundraiser_where}
            AND DATE(d.created_at) BETWEEN :start AND :end
            GROUP BY DATE(d.created_at)
            ORDER BY contribution_date ASC
        ";

        $raw_results = QueryBuilder::query()->raw($sql, [
            'start' => $start,
            'end' => $end
        ]);

        $donor_over_time_service = new ContributorOverTimeService($start_date, $end_date);

        return $donor_over_time_service->prepare_chart_data($raw_results);
    }

    public function get_top_campaigns(DateTime $start_date, DateTime $end_date)
    {
        $campaign_table = WP::POSTS_TABLE;
        $campaign_meta_table = WP::POST_META_TABLE;
        $donations_table = Tables::DONATIONS;
        $campaign_status = Arr::make([CampaignStatus::PUBLISHED, CampaignStatus::FUNDED, CampaignStatus::COMPLETED])
            ->map(function ($status) {
                return "'{$status}'";
            })->join();

        $query = QueryBuilder::query()
            ->table($donations_table . ' as donations')
            ->inner_join(
                "{$campaign_table} as campaigns",
                'donations.campaign_id',
                'campaigns.ID'
            )
            ->join_raw(
                "{$campaign_meta_table} as campaign_status_meta",
                'INNER',
                sprintf(
                    "campaign_status_meta.post_id = donations.campaign_id AND campaign_status_meta.meta_key = '%s' AND campaign_status_meta.meta_value IN (%s) ",
                    growfund_with_prefix('status'),
                    $campaign_status
                )
            )
            ->join_raw(
                "{$campaign_meta_table} as campaign_has_goal_meta",
                'LEFT',
                sprintf(
                    "campaign_has_goal_meta.post_id = donations.campaign_id AND campaign_has_goal_meta.meta_key = '%s' ",
                    growfund_with_prefix('has_goal')
                )
            )
            ->join_raw(
                "{$campaign_meta_table} as campaign_goal_type_meta",
                'LEFT',
                sprintf(
                    "campaign_goal_type_meta.post_id = donations.campaign_id AND campaign_goal_type_meta.meta_key = '%s' ",
                    growfund_with_prefix('goal_type')
                )
            )
            ->join_raw(
                "{$campaign_meta_table} as campaign_goal_amount_meta",
                'LEFT',
                sprintf(
                    "campaign_goal_amount_meta.post_id = donations.campaign_id AND campaign_goal_amount_meta.meta_key = '%s' ",
                    growfund_with_prefix('goal_amount')
                )
            )
            ->join_raw(
                "{$campaign_meta_table} as campaign_images_meta",
                'LEFT',
                sprintf(
                    "campaign_images_meta.post_id = donations.campaign_id AND campaign_images_meta.meta_key = '%s' ",
                    growfund_with_prefix('images')
                )
            )
            ->select([
                "COUNT(donations.id) as total_no_of_donations",
                "COUNT(DISTINCT donations.user_id) + COUNT(DISTINCT CASE WHEN donations.user_id IS NULL THEN donations.email END) + SUM(donations.user_id IS NULL AND donations.email IS NULL) as total_no_of_donors",
                'SUM(amount) as total_donation_amount',
                "campaign_id",
                "campaigns.post_title as campaign_title",
                "campaign_status_meta.meta_value as campaign_status",
                "campaign_has_goal_meta.meta_value as campaign_has_goal",
                "campaign_goal_type_meta.meta_value as campaign_goal_type",
                "campaign_goal_amount_meta.meta_value as campaign_goal_amount",
                "campaign_images_meta.meta_value as campaign_images",

            ])->where('donations.status', '=', DonationStatus::COMPLETED);

        if (growfund_user()->has_active_role(Fundraiser::ROLE)) {
            $campaign_ids = growfund_campaign_ids_by_fundraiser();
            $query->where_in('donations.campaign_id', $campaign_ids);
        }

        if (growfund_user()->has_active_role(Collaborator::ROLE)) {
            $campaign_ids = growfund_campaign_ids_by_collaborator();
            $query->where_in('donations.campaign_id', $campaign_ids);
        }

        $query->where_between('DATE(created_at)', $start_date->format(DateTimeFormats::DB_DATE), $end_date->format(DateTimeFormats::DB_DATE))
            ->group_by('campaign_id')
            ->order_by('total_no_of_donations', 'DESC')
            ->order_by('total_no_of_donors', 'DESC')
            ->limit(5);

        $top_campaigns = $query->get();

        if (empty($top_campaigns)) {
            return [];
        }

        $campaigns = Arr::make($top_campaigns)->map(function ($campaign) {
            $images = !empty($campaign->campaign_images) ? maybe_unserialize($campaign->campaign_images) : [];

            $campaign_dto = new CampaignAnalyticDTO();
            $campaign_dto->id = (string) $campaign->campaign_id;
            $campaign_dto->title = $campaign->campaign_title;
            $campaign_dto->status = $campaign->campaign_status;
            $campaign_dto->has_goal = $campaign->campaign_has_goal;
            $campaign_dto->goal_type = $campaign->campaign_goal_type;
            $campaign_dto->goal_amount = $campaign->campaign_goal_amount;
            $campaign_dto->number_of_contributions = (int) $campaign->total_no_of_donations;
            $campaign_dto->number_of_contributors = $campaign->total_no_of_donors;
            $campaign_dto->fund_raised = $campaign->total_donation_amount;
            $campaign_dto->images = !empty($images) ? MediaAttachment::make_many($images) : [];

            return $campaign_dto;
        });


        return $campaigns->toArray();
    }

    public function get_top_donors(DateTime $start_date, DateTime $end_date)
    {
        $donations_table = Tables::DONATIONS;

        $query = QueryBuilder::query()
            ->table($donations_table . ' as donations')
            ->select([
                "COUNT(*) as total_no_of_donations",
                'SUM(donations.amount) as total_contributions',
                'donations.user_id',
                'donations.email',
                'COALESCE(donations.user_id, donations.email) as group_key',
                'donations.user_info',
            ])->where('status', '=', DonationStatus::COMPLETED);

        if (growfund_user()->has_active_role(Fundraiser::ROLE)) {
            $campaign_ids = growfund_campaign_ids_by_fundraiser();
            $query->where_in('donations.campaign_id', $campaign_ids);
        }

        if (growfund_user()->has_active_role(Collaborator::ROLE)) {
            $campaign_ids = growfund_campaign_ids_by_collaborator();
            $query->where_in('donations.campaign_id', $campaign_ids);
        }

        $query->where_between('DATE(donations.created_at)', $start_date->format(DateTimeFormats::DB_DATE), $end_date->format(DateTimeFormats::DB_DATE))
            ->group_by('group_key')
            ->order_by('total_no_of_donations', 'DESC')
            ->order_by('total_contributions', 'DESC')
            ->limit(5);

        $top_donors = $query->get();

        if (empty($top_donors)) {
            return [];
        }

        return Arr::make($top_donors)->map(function ($donation) {
            $user_info = growfund_is_valid_json($donation->user_info) ? json_decode($donation->user_info, true) : [];
            $donor = DonationDonorDTO::from_array($user_info);

            $donor_dto = new DonorDTO();
            $donor_dto->id = (string) $donation->user_id;
            $donor_dto->first_name = empty($donor->first_name) && empty($donor->last_name) ? 'Unknown' : $donor->first_name;
            $donor_dto->last_name = $donor->last_name;
            $donor_dto->email = $donation->email ?? $donor->email;
            $donor_dto->phone = $donor->phone;
            $donor_dto->image = !empty($donor->image) ? MediaAttachment::make($donor->image) : null;
            $donor_dto->total_contributions = $donation->total_contributions;
            $donor_dto->number_of_contributions = (int) $donation->total_no_of_donations;

            return $donor_dto;
        })->toArray();
    }

    public function get_revenue_breakdown(DateTime $start_date, DateTime $end_date, $campaign_id = null, $fund_id = null)
    {
        $query =  QueryBuilder::query()->table(Tables::DONATIONS . ' as donations')
            ->select([
                "DATE(created_at) as created_at",
                'COUNT(DISTINCT user_id) + COUNT(DISTINCT CASE WHEN user_id IS NULL THEN email END) + SUM(user_id IS NULL AND email IS NULL) as number_of_contributors',
                'SUM(amount) as total_contributions',
                'SUM(processing_fee) as total_processing_fee'
            ]);

        if (growfund_user()->has_active_role(Fundraiser::ROLE)) {
            $campaign_ids = growfund_campaign_ids_by_fundraiser();
            $query->where_in('donations.campaign_id', $campaign_ids);
        }

        if (growfund_user()->has_active_role(Collaborator::ROLE)) {
            $campaign_ids = growfund_campaign_ids_by_collaborator();
            $query->where_in('donations.campaign_id', $campaign_ids);
        }

        if (!empty($campaign_id)) {
            $query->where('campaign_id', $campaign_id);
        }

        if (!empty($fund_id)) {
            $query->where('fund_id', $fund_id);
        }

        $raw_results = $query->where('status', DonationStatus::COMPLETED)
            ->where_between(
                'DATE(created_at)',
                $start_date->format(DateTimeFormats::DB_DATE),
                $end_date->format(DateTimeFormats::DB_DATE)
            )
            ->group_by('DATE(created_at)')
            ->order_by('DATE(created_at)', 'ASC')
            ->get();

        $revenue_service = new RevenueService($start_date, $end_date);

        return $revenue_service->prepare_breakdown_data($raw_results);
    }

    public function get_top_funds(DateTime $start_date, DateTime $end_date)
    {
        $donations_table = Tables::DONATIONS;
        $funds_table = Tables::FUNDS;

        $query = QueryBuilder::query()
            ->table($donations_table . ' as donations')
            ->inner_join("{$funds_table} as funds", "funds.id", "donations.fund_id")
            ->select([
                'SUM(donations.amount) as fund_raised',
                "fund_id",
                'funds.title as fund_title'
            ])->where('donations.status', '=', DonationStatus::COMPLETED);

        if (growfund_user()->has_active_role(Fundraiser::ROLE)) {
            $campaign_ids = growfund_campaign_ids_by_fundraiser();
            $query->where_in('donations.campaign_id', $campaign_ids);
        }

        if (growfund_user()->has_active_role(Collaborator::ROLE)) {
            $campaign_ids = growfund_campaign_ids_by_collaborator();
            $query->where_in('donations.campaign_id', $campaign_ids);
        }

        $query->where('funds.status', '!=', FundStatus::TRASHED)
            ->where_between('DATE(donations.created_at)', $start_date->format(DateTimeFormats::DB_DATE), $end_date->format(DateTimeFormats::DB_DATE))
            ->group_by('fund_id')
            ->order_by('fund_raised', 'DESC')
            ->limit(7);

        $top_funds = $query->get();

        if (empty($top_funds)) {
            return [];
        }

        return Arr::make($top_funds)->map(function ($fund) {
            return [
                'fund_id' => $fund->fund_id,
                'fund_title' => $fund->fund_title,
                'fund_raised' => Money::prepare_for_display($fund->fund_raised)
            ];
        })->toArray();
    }

    public function get_recent_contributions(DateTime $start_date, DateTime $end_date)
    {
        $params_dto = new DonationFilterParamsDTO();
        $params_dto->start_date = $start_date->format(DateTimeFormats::DB_DATE);
        $params_dto->end_date = $end_date->format(DateTimeFormats::DB_DATE);
        $params_dto->limit = 6;

        return $this->donation_service->latest($params_dto);
    }
}
