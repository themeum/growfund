<?php

namespace Growfund\Constants;

defined( 'ABSPATH' ) || exit;

use Growfund\Traits\HasConstants;

class AnalyticsType
{
    use HasConstants;

    const METRICS              = 'metrics';
    const REVENUE_CHART        = 'revenue-chart';
    const TOP_CAMPAIGNS        = 'top-campaigns';
    const TOP_BACKERS          = 'top-backers';
    const BACKER_OVER_TIME     = 'backer-over-time';
    const TOP_DONORS           = 'top-donors';
    const DONOR_OVER_TIME      = 'donor-over-time';
    const REVENUE_BREAKDOWN    = 'revenue-breakdown';
    const TOP_FUNDS            = 'top-funds';
    const RECENT_CONTRIBUTIONS = 'recent-contributions';
}
