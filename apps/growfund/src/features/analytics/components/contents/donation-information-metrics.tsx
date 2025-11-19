import { __ } from '@wordpress/i18n';
import { useMemo } from 'react';
import { useFormContext, useWatch } from 'react-hook-form';

import { LoadingSkeleton } from '@/components/layouts/loading-skeleton';
import {
    type AnalyticsFilter,
    AnalyticType,
    type DonationInformationMetrics as InformationMetricsType,
} from '@/features/analytics/schemas/analytics';
import { useAnalyticsQuery } from '@/features/analytics/services/analytics';
import { MetricsCard } from '@/features/overview/components/ui/metrics-card';
import { useCurrency } from '@/hooks/use-currency';
import { useDebounce } from '@/hooks/use-debounce';
import { toQueryParamSafe } from '@/lib/date';
import { cn } from '@/lib/utils';

const DonationInformationMetrics = ({
  hasNoColor = false,
  invisibleGrowth = false,
  campaignId,
}: {
  hasNoColor?: boolean;
  invisibleGrowth?: boolean;
  campaignId?: string;
}) => {
  const { toCurrencyCompact } = useCurrency();
  const form = useFormContext<AnalyticsFilter>();

  const dateRange = useDebounce(useWatch({ control: form.control, name: 'date_range' }));

  const metricAnalyticsQuery = useAnalyticsQuery<InformationMetricsType>(AnalyticType.Metrics, {
    start_date: dateRange?.from ? toQueryParamSafe(dateRange.from) : undefined,
    end_date: dateRange?.to ? toQueryParamSafe(dateRange.to) : undefined,
    campaign_id: campaignId,
  });

  const metrics = useMemo(() => {
    const metrics = metricAnalyticsQuery.data;
    if (!metrics) return [];

    return [
      {
        label: __('Total Donation', 'growfund'),
        amount: toCurrencyCompact(metrics.total_donation.amount.toFixed(2)),
        growth: !invisibleGrowth ? metrics.total_donation.growth : undefined,
        className: !hasNoColor && 'growfund-bg-exception-1',
      },
      {
        label: __('Net Donation', 'growfund'),
        amount: toCurrencyCompact(metrics.net_donation.amount.toFixed(2)),
        growth: !invisibleGrowth ? metrics.net_donation.growth : undefined,
        className: !hasNoColor && 'growfund-bg-exception-2',
      },
      {
        label: __('Average Donation', 'growfund'),
        amount: toCurrencyCompact(metrics.average_donation.amount.toFixed(2)),
        growth: !invisibleGrowth ? metrics.average_donation.growth : undefined,
        className: !hasNoColor && 'growfund-bg-background-fill-special-2-secondary',
      },
      {
        label: __('Total Donors', 'growfund'),
        amount: metrics.total_donors.amount.toString(),
        growth: !invisibleGrowth ? metrics.total_donors.growth : undefined,
        className: !hasNoColor && 'growfund-bg-exception-3',
      },
    ];
  }, [metricAnalyticsQuery.data, toCurrencyCompact, hasNoColor, invisibleGrowth]);

  return (
    <div className="growfund-grid growfund-grid-cols-2 lg:growfund-grid-cols-4 growfund-items-center growfund-gap-8">
      {metrics.map((metric, index) => {
        const { className, ...data } = metric;
        return (
          <LoadingSkeleton
            key={index}
            loading={metricAnalyticsQuery.isFetching || metricAnalyticsQuery.isLoading}
            className="growfund-w-full growfund-rounded-3xl growfund-shadow-sm growfund-p-0 growfund-min-w-48 growfund-h-28"
            skeletonClassName="growfund-w-full growfund-flex growfund-rounded-3xl growfund-min-w-48 growfund-h-28 "
          >
            <MetricsCard data={data} className={cn('growfund-rounded-3xl', className)} />
          </LoadingSkeleton>
        );
      })}
    </div>
  );
};

export default DonationInformationMetrics;
