<?php

namespace Growfund\Services\Analytics;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\DateTimeFormats;
use Growfund\Constants\Status\CampaignStatus;
use Growfund\Constants\Status\PledgeStatus;
use Growfund\Constants\Tables;
use Growfund\Constants\WP;
use Growfund\DTO\Backer\BackerDTO;
use Growfund\DTO\Campaign\CampaignAnalyticDTO;
use Growfund\DTO\Pledge\PledgeFilterParamsDTO;
use Growfund\QueryBuilder;
use Growfund\Services\CampaignService;
use Growfund\Services\PledgeService;
use Growfund\Supports\Arr;
use Growfund\Supports\MediaAttachment;
use Growfund\Supports\Money;
use Growfund\Supports\Utils;
use DateTime;

/**
 * Class CampaignAnalyticService
 * @since 1.0.0
 */
class CampaignAnalyticService
{
    /** @var PledgeService */
    protected $pledge_service;

    public function __construct()
    {
        $this->pledge_service = new PledgeService();
    }

    public function get_metrics(DateTime $start_date, DateTime $end_date, $campaign_id = null)
    {
        $interval = $start_date->diff($end_date);

        $previous_end = clone $start_date;
        $previous_end->modify('-1 day');

        $previous_start = clone $previous_end;
        $previous_start->sub($interval);

        $start_date = $start_date->format(DateTimeFormats::DB_DATE);
        $end_date = $end_date->format(DateTimeFormats::DB_DATE);
        $previous_start = $previous_start->format(DateTimeFormats::DB_DATE);
        $previous_end = $previous_end->format(DateTimeFormats::DB_DATE);

        $campaign_service = new CampaignService();

        // Current period values
        $total_pledges_amount = (int) $this->pledge_service->get_total_pledges_amount_for_campaign($campaign_id, $start_date, $end_date);
        $total_backed_amount = (int) $this->pledge_service->get_total_backed_amount_for_campaign($campaign_id, $start_date, $end_date);
        $net_backed_amount = (int) $this->pledge_service->get_net_backed_amount_for_campaign($campaign_id, $start_date, $end_date);
        $total_backers = (int) $this->pledge_service->get_total_backers_count_for_campaign($campaign_id, $start_date, $end_date);
        $average_backed_amount = (int) $this->pledge_service->get_average_backed_amount_for_campaign($campaign_id, $start_date, $end_date);
        $successful_campaigns = (int) $campaign_service->get_count_of_successful_campaigns($start_date, $end_date);
        $failed_campaigns = (int) $campaign_service->get_count_of_failed_campaigns($start_date, $end_date);
        $total_campaigns = $successful_campaigns + $failed_campaigns;
        $campaign_success_rate = $total_campaigns > 0 ? round($successful_campaigns / $total_campaigns * 100, 2) : 0;

        // Previous period values
        $prev_pledges_amount = (int) $this->pledge_service->get_total_pledges_amount_for_campaign($campaign_id, $previous_start, $previous_end);
        $prev_backed_amount = (int) $this->pledge_service->get_total_backed_amount_for_campaign($campaign_id, $previous_start, $previous_end);
        $prev_net_backed_amount = (int) $this->pledge_service->get_net_backed_amount_for_campaign($campaign_id, $previous_start, $previous_end);
        $prev_backers = (int) $this->pledge_service->get_total_backers_count_for_campaign($campaign_id, $previous_start, $previous_end);
        $prev_average_backed_amount = (int) $this->pledge_service->get_average_backed_amount_for_campaign($campaign_id, $previous_start, $previous_end);
        $prev_successful_campaigns = (int) $campaign_service->get_count_of_successful_campaigns($previous_start, $previous_end);
        $prev_failed_campaigns = (int) $campaign_service->get_count_of_failed_campaigns($previous_start, $previous_end);
        $prev_total_campaigns = $prev_successful_campaigns + $prev_failed_campaigns;
        $prev_campaign_success_rate = $prev_total_campaigns > 0 ? round($prev_successful_campaigns / $prev_total_campaigns * 100, 2) : 0;

        return [
            'pledged_amount' => [
                'amount' => Money::prepare_for_display($total_pledges_amount),
                'growth' => Utils::calculate_growth_in_percent($total_pledges_amount, $prev_pledges_amount),
            ],
            'total_backed_amount' => [
                'amount' => Money::prepare_for_display($total_backed_amount),
                'growth' => Utils::calculate_growth_in_percent($total_backed_amount, $prev_backed_amount),
            ],
            'net_backed_amount' => [
                'amount' => Money::prepare_for_display($net_backed_amount),
                'growth' => Utils::calculate_growth_in_percent($net_backed_amount, $prev_net_backed_amount),
            ],
            'total_backers' => [
                'amount' => $total_backers,
                'growth' => Utils::calculate_growth_in_percent($total_backers, $prev_backers),
            ],
            'average_backed_amount' => [
                'amount' => Money::prepare_for_display($average_backed_amount),
                'growth' => Utils::calculate_growth_in_percent($average_backed_amount, $prev_average_backed_amount),
            ],
            'successful_campaigns' => [
                'amount' => $successful_campaigns,
                'growth' => Utils::calculate_growth_in_percent($successful_campaigns, $prev_successful_campaigns),
            ],
            'failed_campaigns' => [
                'amount' => $failed_campaigns,
                'growth' => Utils::calculate_growth_in_percent($failed_campaigns, $prev_failed_campaigns),
            ],
            'campaign_success_rate' => [
                'amount' => $campaign_success_rate,
                'growth' => Utils::calculate_growth_in_percent($campaign_success_rate, $prev_campaign_success_rate),
            ],
        ];
    }

    /**
     * Generates revenue chart data for a specific campaign within a specified date range.
     *
     * This method retrieves and aggregates pledge data for a given campaign,
     * organizing it by time intervals (daily, weekly, or monthly) based on the
     * span of the date range. The returned data is formatted for use in revenue
     * chart visualizations.
     *
     * @param DateTime $start_date The start date for the data range in DateTimeFormats::DB_DATE format.
     * @param DateTime $end_date The end date for the data range in DateTimeFormats::DB_DATE format.
     * @param int $campaign_id The ID of the campaign for which to generate chart data.
     *
     * @return array An array of associative arrays, each containing 'date' and 'revenue' keys, representing the revenue data for each time interval.
     */
    public function get_revenue_chart_data(DateTime $start_date, DateTime $end_date, $campaign_id = null)
    {
        $pledges_table = Tables::PLEDGES;

        $query =  QueryBuilder::query()->table($pledges_table . ' as pledges')
            ->select([
                "DATE(created_at) as created_at",
                "SUM(amount + bonus_support_amount + shipping_cost) AS total_contributions",
            ]);

        if (growfund_user()->is_fundraiser()) {
            $campaign_ids = growfund_get_all_campaign_ids_by_fundraiser();
            $query->where_in('campaign_id', $campaign_ids);
        }

        if (!empty($campaign_id)) {
            $query->where('campaign_id', $campaign_id);
        }

        $raw_results = $query->where_in('status', [PledgeStatus::COMPLETED, PledgeStatus::BACKED])
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
     * Get top campaigns.
     *
     * @param DateTime $start_date
     * @param DateTime $end_date
     * @return array
     */
    public function get_top_campaigns(DateTime $start_date, DateTime $end_date)
    {
        $start_date = $start_date->format(DateTimeFormats::DB_DATE);
        $end_date = $end_date->format(DateTimeFormats::DB_DATE);

        $postmeta_table = WP::POST_META_TABLE;
        $posts_table = WP::POSTS_TABLE;
        $pledges_table = Tables::PLEDGES;

        $query = QueryBuilder::query()
            ->table($pledges_table . ' as pledges')
            ->select([
                "COUNT(pledges.id) as total_no_of_pledges",
                "COUNT(DISTINCT pledges.user_id) + COUNT(DISTINCT CASE WHEN pledges.user_id IS NULL THEN pledges.email END) + SUM(pledges.user_id IS NULL AND pledges.email IS NULL) as total_no_of_backers",
                'SUM(pledges.amount + pledges.bonus_support_amount + pledges.shipping_cost) as total_pledge_amount',
                "pledges.campaign_id",
                "campaigns.post_title as campaign_title",
                "status_meta.meta_value as campaign_status",
                "has_goal_meta.meta_value as campaign_has_goal",
                "goal_type_meta.meta_value as campaign_goal_type",
                "goal_amount_meta.meta_value as campaign_goal_amount",
                "images_meta.meta_value as campaign_images",
            ])
            ->join_raw(
                "{$posts_table} as campaigns",
                "INNER",
                "pledges.campaign_id = campaigns.ID"
            )
            ->join_raw(
                "{$postmeta_table} as status_meta",
                "INNER",
                sprintf("status_meta.post_id = pledges.campaign_id AND status_meta.meta_key = '%s'", growfund_with_prefix('status'))
            )
            ->join_raw(
                "{$postmeta_table} as has_goal_meta",
                "INNER",
                sprintf("has_goal_meta.post_id = pledges.campaign_id AND has_goal_meta.meta_key = '%s'", growfund_with_prefix('has_goal'))
            )
            ->join_raw(
                "{$postmeta_table} as goal_type_meta",
                "INNER",
                sprintf("goal_type_meta.post_id = pledges.campaign_id AND goal_type_meta.meta_key = '%s'", growfund_with_prefix('goal_type'))
            )
            ->join_raw(
                "{$postmeta_table} as goal_amount_meta",
                "INNER",
                sprintf("goal_amount_meta.post_id = pledges.campaign_id AND goal_amount_meta.meta_key = '%s'", growfund_with_prefix('goal_amount'))
            )
            ->join_raw(
                "{$postmeta_table} as images_meta",
                "INNER",
                sprintf("images_meta.post_id = pledges.campaign_id AND images_meta.meta_key = '%s'", growfund_with_prefix('images'))
            )
            ->where_in(
                'status_meta.meta_value',
                [CampaignStatus::PUBLISHED, CampaignStatus::FUNDED, CampaignStatus::COMPLETED]
            )->where_between('DATE(pledges.created_at)', $start_date, $end_date);

        if (growfund_user()->is_fundraiser()) {
            $campaign_ids = growfund_get_all_campaign_ids_by_fundraiser();
            $query->where_in('pledges.campaign_id', $campaign_ids);
        }

        $query->group_by('pledges.campaign_id')
            ->order_by('total_no_of_pledges', 'DESC')
            ->order_by('total_no_of_backers', 'DESC')
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
            $campaign_dto->number_of_contributions = (int) $campaign->total_no_of_pledges;
            $campaign_dto->number_of_contributors = (int) $campaign->total_no_of_backers;
            $campaign_dto->fund_raised = $campaign->total_pledge_amount;
            $campaign_dto->images = !empty($images) ? MediaAttachment::make_many($images) : [];

            return $campaign_dto;
        });

        return $campaigns->toArray();
    }

    /**
     * Get top backers
     * 
     * @param DateTime $start_date
     * @param DateTime $end_date
     */
    public function get_top_backers(DateTime $start_date, DateTime $end_date)
    {
        $pledges_table = Tables::PLEDGES;
        $users_table = WP::USERS_TABLE;
        $user_meta_table = WP::USER_META_TABLE;

        $query = QueryBuilder::query()
            ->table($pledges_table . ' as pledges')
            ->select([
                'COUNT(*) as total_no_of_pledges',
                'SUM(amount + bonus_support_amount + shipping_cost) as total_contributions',
                'pledges.user_id',
                'users.user_email',
                'users.display_name',
                'first_name_meta.meta_value as first_name',
                'last_name_meta.meta_value as last_name',
                'phone_meta.meta_value as phone',
                'image_meta.meta_value as image'
            ])
            ->join_raw(
                "{$users_table} as users",
                "INNER",
                "pledges.user_id = users.ID"
            )
            ->join_raw(
                "{$user_meta_table} as first_name_meta",
                "LEFT",
                "first_name_meta.user_id = pledges.user_id AND first_name_meta.meta_key = 'first_name'"
            )
            ->join_raw(
                "{$user_meta_table} as last_name_meta",
                "LEFT",
                "last_name_meta.user_id = pledges.user_id AND last_name_meta.meta_key = 'last_name'"
            )
            ->join_raw(
                "{$user_meta_table} as phone_meta",
                "LEFT",
                sprintf("phone_meta.user_id = pledges.user_id AND phone_meta.meta_key = '%s'", growfund_with_prefix('phone'))
            )
            ->join_raw(
                "{$user_meta_table} as image_meta",
                "LEFT",
                sprintf("image_meta.user_id = pledges.user_id AND image_meta.meta_key = '%s'", growfund_with_prefix('image'))
            )
            ->where_in('pledges.status', [PledgeStatus::PENDING, PledgeStatus::IN_PROGRESS, PledgeStatus::BACKED, PledgeStatus::COMPLETED])
            ->where_between('DATE(pledges.created_at)', $start_date->format(DateTimeFormats::DB_DATE), $end_date->format(DateTimeFormats::DB_DATE));

        if (growfund_user()->is_fundraiser()) {
            $campaign_ids = growfund_get_all_campaign_ids_by_fundraiser();
            $query->where_in('pledges.campaign_id', $campaign_ids);
        }

        $query->group_by('pledges.user_id')
            ->order_by('total_no_of_pledges', 'DESC')
            ->order_by('total_contributions', 'DESC')
            ->limit(5);

        $top_backers = $query->get();

        if (empty($top_backers)) {
            return [];
        }

        return Arr::make($top_backers)->map(function ($backer) {
            if (empty($backer->first_name) && empty($backer->last_name)) {
                $backer->first_name = $backer->display_name;
                $backer->last_name = '';
            }
            
            $backer_dto = new BackerDTO();
            $backer_dto->id = (string) $backer->user_id;
            $backer_dto->first_name = $backer->first_name;
            $backer_dto->last_name = $backer->last_name;
            $backer_dto->email = $backer->user_email;
            $backer_dto->phone = $backer->phone;
            $backer_dto->image = !empty($backer->image) ? MediaAttachment::make($backer->image) : null;
            $backer_dto->total_contributions = $backer->total_contributions;
            $backer_dto->number_of_contributions = (int) $backer->total_no_of_pledges;

            return $backer_dto;
        })->toArray();
    }

    public function get_revenue_breakdown(DateTime $start_date, DateTime $end_date, $campaign_id = null)
    {
        $query =  QueryBuilder::query()->table(Tables::PLEDGES . ' as pledges')
            ->select([
                "DATE(created_at) as created_at",
                'COUNT(DISTINCT user_id) + COUNT(DISTINCT CASE WHEN user_id IS NULL THEN email END) + SUM(user_id IS NULL AND email IS NULL) as number_of_contributors',
                'SUM(amount + bonus_support_amount + shipping_cost) as pledged_amount',
                sprintf(
                    "SUM(CASE WHEN status IN (%s) THEN (amount + bonus_support_amount + shipping_cost) ELSE 0 END) AS total_contributions",
                    Arr::make([PledgeStatus::COMPLETED, PledgeStatus::BACKED])
                        ->map(function ($status) {
                            return "'{$status}'";
                        })->join()
                ),
                'SUM(processing_fee) as total_processing_fee'
            ]);

        if (growfund_user()->is_fundraiser()) {
            $campaign_ids = growfund_get_all_campaign_ids_by_fundraiser();
            $query->where_in('campaign_id', $campaign_ids);
        }

        if (!empty($campaign_id)) {
            $query->where('campaign_id', $campaign_id);
        }

        $raw_results = $query->where_in('status', [PledgeStatus::COMPLETED, PledgeStatus::BACKED])
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

    public function get_recent_contributions(DateTime $start_date, DateTime $end_date)
    {
        $params_dto = new PledgeFilterParamsDTO();
        $params_dto->start_date = $start_date->format(DateTimeFormats::DB_DATE);
        $params_dto->end_date = $end_date->format(DateTimeFormats::DB_DATE);
        $params_dto->status = [PledgeStatus::PENDING, PledgeStatus::IN_PROGRESS, PledgeStatus::BACKED, PledgeStatus::COMPLETED];
        $params_dto->limit = 6;

        return $this->pledge_service->latest($params_dto);
    }

    /**
     * Retrieves backer over time chart data.
     *
     * @param DateTime $start_date The start date for the data range in 'Y-m-d' format. If null, defaults to 30 days before the end date.
     * @param DateTime $end_date The end date for the data range in 'Y-m-d' format. If null, defaults to the current date.
     *
     * @return array An array of objects containing the date label, first time total, and recurring total for each interval.
     */
    public function get_backer_over_time_chart_data(DateTime $start_date, DateTime $end_date)
    {
        $pledges_table = QueryBuilder::prefix(Tables::PLEDGES);

        $fundraiser_where = '';

        if (growfund_user()->is_fundraiser()) {
            $campaign_ids = implode(',', array_map('intval', growfund_get_all_campaign_ids_by_fundraiser()));
            
            if (!empty($campaign_ids)) {
                $fundraiser_where = "AND d.campaign_id IN ($campaign_ids)";
            }
        }

        $start = $start_date->format(DateTimeFormats::DB_DATE);
        $end = $end_date->format(DateTimeFormats::DB_DATE);

        $sql = "SELECT DATE(d.created_at) AS contribution_date,
                SUM(CASE WHEN d.id = fd.first_id THEN 1 ELSE 0 END) AS first_time_total,
                SUM(CASE WHEN d.id != fd.first_id THEN 1 ELSE 0 END) AS recurring_total
            FROM {$pledges_table} AS d
            INNER JOIN (
                SELECT user_id, MIN(id) AS first_id
                FROM {$pledges_table}
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

        $backer_over_time_service = new ContributorOverTimeService($start_date, $end_date);

        return $backer_over_time_service->prepare_chart_data($raw_results);
    }
}
