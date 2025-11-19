<?php

namespace Growfund\Controllers\API;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\AnalyticsType;
use Growfund\Contracts\Request;
use Growfund\Exceptions\ValidationException;
use Growfund\Services\AnalyticService;
use Growfund\Supports\Date;
use Growfund\Validation\Validator;

/**
 * @since 1.0.0
 */
class AnalyticsController
{
    /** @var AnalyticService */
    protected $service;

    public function __construct(AnalyticService $service)
    {
        $this->service = $service;
    }

    public function get_analytics(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        if ($validator->is_failed()) {
            throw new ValidationException('Please provide valid dates');
        }

        $start_date = $request->get_date('start_date');
        $end_date = $request->get_date('end_date');

        if (empty($start_date) && empty($end_date)) {
            list($start_date, $end_date) = Date::start_and_end_date_of_last_thirty_days();
        }

        if (empty($end_date)) {
            $end_date = $start_date;
        }

        $type = $request->get_string('type');
        $campaign_id = $request->get_string('campaign_id');

        $data = null;

        switch ($type) {
            case AnalyticsType::METRICS:
                $data = $this->service->get_metrics($start_date, $end_date, $campaign_id);
                break;
            case AnalyticsType::REVENUE_CHART:
                $data = $this->service->get_revenue_chart($start_date, $end_date, $campaign_id);
                break;
            case AnalyticsType::TOP_CAMPAIGNS:
                $data = $this->service->get_top_campaigns($start_date, $end_date);
                break;
            case AnalyticsType::TOP_BACKERS:
                $data = $this->service->get_top_backers($start_date, $end_date);
                break;
            case AnalyticsType::BACKER_OVER_TIME:
                $data = $this->service->get_backer_over_time_chart($start_date, $end_date);
                break;
            case AnalyticsType::TOP_DONORS:
                $data = $this->service->get_top_donors($start_date, $end_date);
                break;
            case AnalyticsType::DONOR_OVER_TIME:
                $data = $this->service->get_donor_over_time_chart($start_date, $end_date);
                break;
            case AnalyticsType::TOP_FUNDS:
                $data = $this->service->get_top_funds($start_date, $end_date);
                break;
            case AnalyticsType::RECENT_CONTRIBUTIONS:
                $data = $this->service->get_recent_contributions($start_date, $end_date);
                break;
            default:
                break;
        }

        return growfund_response()->json([
            'data' => $data
        ]);
    }
}
