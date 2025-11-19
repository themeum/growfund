<?php

namespace Growfund\Supports;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\Campaign\FundSelectionType;
use Growfund\Core\AppSettings;
use Growfund\Services\CampaignService;
use Growfund\Services\FundService;

class Fund
{
    public static function get_funds_for_donation($campaign_id)
    {
        if (empty($campaign_id)) {
            return [];
        }

        $fund_service = new FundService();
        $campaign_service = new CampaignService();

        $campaign_dto = $campaign_service->get_by_id($campaign_id);
        $is_fund_allowed = growfund_settings(AppSettings::CAMPAIGNS)->allow_fund();

        if (!$is_fund_allowed) {
            return [];
        }

        if ($campaign_dto->fund_selection_type === FundSelectionType::FIXED) {
            return [];
        }

        if (empty($campaign_dto->fund_choices)) {
            return [];
        }

        $funds = $fund_service->get_by_ids($campaign_dto->fund_choices);

        if (empty($funds)) {
            return [];
        }

        return Arr::make($funds)->map(function ($fund) {
            return [
                'id' => (string) $fund->id,
                'title' => $fund->title,
                'is_default' => (bool) $fund->is_default,
            ];
        })->toArray();
    }
}
