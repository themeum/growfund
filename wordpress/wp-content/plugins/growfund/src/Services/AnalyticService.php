<?php

namespace Growfund\Services;

defined( 'ABSPATH' ) || exit;

use Growfund\Http\Response;
use Growfund\Services\Analytics\CampaignAnalyticService;
use Growfund\Services\Analytics\DonationAnalyticService;
use DateTime;
use Exception;

/**
 * Class AnalyticService
 * @since 1.0.0
 */
class AnalyticService
{
    /** @var CampaignAnalyticService */
    protected $campaign_analytic_service;

    /** @var DonationAnalyticService */
    protected $donation_analytic_service;

	public function __construct()
	{
		$this->campaign_analytic_service = new CampaignAnalyticService();
		$this->donation_analytic_service = new DonationAnalyticService();
	}

	public function get_metrics(string $start_date, string $end_date, $campaign_id = null)
	{
		$start_date = new DateTime($start_date);
		$end_date = new DateTime($end_date);

		if (growfund_app()->is_donation_mode()) {
			return $this->donation_analytic_service->get_metrics($start_date, $end_date, $campaign_id);
		}

		return $this->campaign_analytic_service->get_metrics($start_date, $end_date, $campaign_id);
	}

	public function get_revenue_chart(string $start_date, string $end_date, $campaign_id)
	{   
		$start_date = new DateTime($start_date);
		$end_date = new DateTime($end_date);

		if (growfund_app()->is_donation_mode()) {
			return $this->donation_analytic_service->get_revenue_chart_data($start_date, $end_date, $campaign_id);
		}

		return $this->campaign_analytic_service->get_revenue_chart_data($start_date, $end_date, $campaign_id);
	}
    
	public function get_donor_over_time_chart(string $start_date, string $end_date)
	{
		$start_date = new DateTime($start_date);
		$end_date = new DateTime($end_date);

		if (!growfund_app()->is_donation_mode()) {
			throw new Exception(esc_html__('Method not allowed', 'growfund'), (int) Response::METHOD_NOT_ALLOWED);
		}

        return $this->donation_analytic_service->get_donor_over_time_chart_data($start_date, $end_date);
    }
	
    public function get_backer_over_time_chart(string $start_date, string $end_date)
	{
		$start_date = new DateTime($start_date);
		$end_date = new DateTime($end_date);

		if (growfund_app()->is_donation_mode()) {
			throw new Exception(esc_html__('Method not allowed', 'growfund'), (int) Response::METHOD_NOT_ALLOWED);
        }

        return $this->campaign_analytic_service->get_backer_over_time_chart_data($start_date, $end_date);
    }

    public function get_top_campaigns(string $start_date, string $end_date)
    {
        $start_date = new DateTime($start_date);
        $end_date = new DateTime($end_date);

        if (growfund_app()->is_donation_mode()) {
            return $this->donation_analytic_service->get_top_campaigns($start_date, $end_date);
        }

        return $this->campaign_analytic_service->get_top_campaigns($start_date, $end_date);
    }

    public function get_top_backers(string $start_date, string $end_date)
    {
        if (growfund_app()->is_donation_mode()) {
			throw new Exception(esc_html__('Method not allowed', 'growfund'), (int) Response::METHOD_NOT_ALLOWED);
        }

        $start_date = new DateTime($start_date);
        $end_date = new DateTime($end_date);

        return $this->campaign_analytic_service->get_top_backers($start_date, $end_date);
    }

    public function get_top_donors(string $start_date, string $end_date)
    {
        if (!growfund_app()->is_donation_mode()) {
			throw new Exception(esc_html__('Method not allowed', 'growfund'), (int) Response::METHOD_NOT_ALLOWED);
        }

        $start_date = new DateTime($start_date);
        $end_date = new DateTime($end_date);

        return $this->donation_analytic_service->get_top_donors($start_date, $end_date);
    }

    public function get_top_funds(string $start_date, string $end_date)
    {
        if (!growfund_app()->is_donation_mode()) {
            throw new Exception(esc_html__('Method not allowed', 'growfund'), (int) Response::METHOD_NOT_ALLOWED);
        }

        $start_date = new DateTime($start_date);
        $end_date = new DateTime($end_date);

        return $this->donation_analytic_service->get_top_funds($start_date, $end_date);
    }

    public function get_recent_contributions(string $start_date, string $end_date)
    {
        $start_date = new DateTime($start_date);
        $end_date = new DateTime($end_date);

        if (growfund_app()->is_donation_mode()) {
            return $this->donation_analytic_service->get_recent_contributions($start_date, $end_date);
        }

        return $this->campaign_analytic_service->get_recent_contributions($start_date, $end_date);
    }
}
