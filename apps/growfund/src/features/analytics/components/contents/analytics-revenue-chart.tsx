import { useFormContext, useWatch } from 'react-hook-form';

import {
    type AnalyticsFilter,
    AnalyticType,
    type RevenueChart as RevenueChartType,
} from '@/features/analytics/schemas/analytics';
import { useAnalyticsQuery } from '@/features/analytics/services/analytics';
import { useCampaignId } from '@/features/campaigns/contexts/campaignId-context';
import RevenueChart from '@/features/overview/components/revenue-chart';
import { useDebounce } from '@/hooks/use-debounce';
import { toQueryParamSafe } from '@/lib/date';
const AnalyticsRevenueChart = () => {
  const form = useFormContext<AnalyticsFilter>();
  const { campaignId } = useCampaignId();

  const dateRange = useDebounce(useWatch({ control: form.control, name: 'date_range' }));

  const revenueChartAnalyticsQuery = useAnalyticsQuery<RevenueChartType[]>(
    AnalyticType.RevenueChart,
    {
      start_date: dateRange?.from ? toQueryParamSafe(dateRange.from) : undefined,
      end_date: dateRange?.to ? toQueryParamSafe(dateRange.to) : undefined,
      campaign_id: campaignId,
    },
  );

  return (
    <RevenueChart
      className="growfund-rounded-3xl"
      data={revenueChartAnalyticsQuery.data ?? []}
      loading={revenueChartAnalyticsQuery.isFetching || revenueChartAnalyticsQuery.isLoading}
    />
  );
};

export default AnalyticsRevenueChart;
