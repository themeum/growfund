import { __ } from '@wordpress/i18n';
import { useMemo } from 'react';
import { useFormContext, useWatch } from 'react-hook-form';

import { LoadingSkeleton } from '@/components/layouts/loading-skeleton';
import { RouteConfig } from '@/config/route-config';
import {
    type AnalyticsFilter,
    AnalyticType,
    type CampaignInformationMetrics as InformationMetricsType,
} from '@/features/analytics/schemas/analytics';
import { useAnalyticsQuery } from '@/features/analytics/services/analytics';
import { MetricsCard } from '@/features/overview/components/ui/metrics-card';
import { useCurrency } from '@/hooks/use-currency';
import { useCurrentPath } from '@/hooks/use-current-path';
import { useDebounce } from '@/hooks/use-debounce';
import { toQueryParamSafe } from '@/lib/date';
import { cn } from '@/lib/utils';

const CampaignInformationMetrics = ({
  hasNoColor = false,
  invisibleGrowth = false,
  campaignId,
}: {
  hasNoColor?: boolean;
  invisibleGrowth?: boolean;
  campaignId?: string;
}) => {
  const currentPath = useCurrentPath();
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

    const basics = [
      {
        label: __('Pledged Amount', 'growfund'),
        amount: toCurrencyCompact(metrics.pledged_amount.amount.toFixed(2)),
        growth: !invisibleGrowth ? metrics.pledged_amount.growth : undefined,
        className: !hasNoColor && 'growfund-bg-exception-21',
        tooltip: __(
          'Total amount committed by backers including pending, in-progress, backed, and completed pledges. This includes the base amount, bonus support, and shipping costs.',
          'growfund',
        ),
      },
      {
        label: __('Total Backed Amount', 'growfund'),
        amount: toCurrencyCompact(metrics.total_backed_amount.amount.toFixed(2)),
        growth: !invisibleGrowth ? metrics.total_backed_amount.growth : undefined,
        className: !hasNoColor && 'growfund-bg-exception-1',
        tooltip: __(
          'Total amount from successfully processed pledges with "backed" or "completed" status. This represents confirmed funding that has been collected.',
          'growfund',
        ),
      },
      {
        label: __('Net Backed Amount', 'growfund'),
        amount: toCurrencyCompact(metrics.net_backed_amount.amount),
        growth: !invisibleGrowth ? metrics.net_backed_amount.growth : undefined,
        className: !hasNoColor && 'growfund-bg-exception-2',
        tooltip: __(
          'Total backed amount minus processing fees. This is the actual amount you will receive after payment processing costs are deducted.',
          'growfund',
        ),
      },
      {
        label: __('Total Backers', 'growfund'),
        amount: metrics.total_backers.amount.toString(),
        growth: !invisibleGrowth ? metrics.total_backers.growth : undefined,
        className: !hasNoColor && 'growfund-bg-exception-3',
        tooltip: __(
          'Number of unique supporters who have made successful pledges with "backed" or "completed" status during this period.',
          'growfund',
        ),
      },
    ];

    if (currentPath !== RouteConfig.Analytics.template) {
      return basics;
    }

    return [
      ...basics,
      {
        label: __('Average Backed', 'growfund'),
        amount: toCurrencyCompact(metrics.average_backed_amount.amount.toFixed(2)),
        growth: !invisibleGrowth ? metrics.average_backed_amount.growth : undefined,
        className: !hasNoColor && 'growfund-bg-background-fill-special-2-secondary',
        tooltip: __(
          'Average amount per successful pledge (backed or completed status). Calculated by dividing total backed amount by number of pledges("backed" or "completed").',
          'growfund',
        ),
      },
      {
        label: __('Successful Campaigns', 'growfund'),
        amount: metrics.successful_campaigns.amount.toString(),
        growth: !invisibleGrowth ? metrics.successful_campaigns.growth : undefined,
        className: !hasNoColor && 'growfund-bg-exception-4',
        tooltip: __(
          'Number of campaigns that reached "funded" or "completed" status during this period. These campaigns met their funding goals.',
          'growfund',
        ),
      },
      {
        label: __('Failed Campaigns', 'growfund'),
        amount: metrics.failed_campaigns.amount.toString(),
        growth: !invisibleGrowth ? metrics.failed_campaigns.growth : undefined,
        className: !hasNoColor && 'growfund-bg-exception-19',
        tooltip: __(
          'Number of campaigns that failed to meet their goals or were cancelled. Includes campaigns that ended without reaching their target amount.',
          'growfund',
        ),
      },
      {
        label: __('Campaign Success Rate', 'growfund'),
        amount: `${metrics.campaign_success_rate.amount.toString()}%`,
        growth: !invisibleGrowth ? metrics.campaign_success_rate.growth : undefined,
        className: !hasNoColor && 'growfund-bg-exception-22',
        tooltip: __(
          'Percentage of campaigns that successfully reached their funding goals. Calculated as successful campaigns divided by total campaigns (successful + failed).',
          'growfund',
        ),
      },
    ];
  }, [metricAnalyticsQuery.data, currentPath, toCurrencyCompact, hasNoColor, invisibleGrowth]);

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

export default CampaignInformationMetrics;
