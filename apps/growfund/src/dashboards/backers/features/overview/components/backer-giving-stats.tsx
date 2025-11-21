import { __ } from '@wordpress/i18n';

import { ErrorIcon } from '@/app/icons';
import { EmptyState, EmptyStateDescription } from '@/components/empty-state';
import { ErrorState, ErrorStateDescription } from '@/components/error-state';
import { Skeleton } from '@/components/ui/skeleton';
import { useBackerGivingStatsQuery } from '@/features/backers/services/backer';
import { MetricsCard } from '@/features/overview/components/ui/metrics-card';
import { useCurrency } from '@/hooks/use-currency';
import { matchQueryStatus } from '@/utils/match-query-status';

const BackerGivingStats = ({ backerId }: { backerId?: string }) => {
  const { toCurrencyCompact } = useCurrency();
  const backerGivingStatsQuery = useBackerGivingStatsQuery(backerId);

  return matchQueryStatus(backerGivingStatsQuery, {
    Loading: (
      <div className="growfund-grid growfund-grid-cols-4 growfund-gap-4">
        <Skeleton className="growfund-h-[6.75rem] growfund-w-full growfund-rounded-sm" animate />
        <Skeleton className="growfund-h-[6.75rem] growfund-w-full growfund-rounded-sm" animate />
        <Skeleton className="growfund-h-[6.75rem] growfund-w-full growfund-rounded-sm" animate />
        <Skeleton className="growfund-h-[6.75rem] growfund-w-full growfund-rounded-sm" animate />
      </div>
    ),
    Error: (
      <ErrorState className="growfund-mt-10">
        <ErrorIcon />
        <ErrorStateDescription>
          {__('Error loading backer giving stats', 'growfund')}
        </ErrorStateDescription>
      </ErrorState>
    ),
    Empty: (
      <EmptyState className="growfund-mt-10">
        <EmptyStateDescription className="growfund-flex growfund-flex-col growfund-items-center">
          {__('No backer giving stats', 'growfund')}
        </EmptyStateDescription>
      </EmptyState>
    ),
    Success: ({ data }) => {
      return (
        <div className="growfund-space-y-4">
          <h5 className="growfund-typo-h5 growfund-text-fg-primary">
            {__('My giving stats', 'growfund')}
          </h5>
          <div className="growfund-grid growfund-grid-cols-4 growfund-gap-4">
            <MetricsCard
              data={{
                label: __('Pledged Amount', 'growfund'),
                amount: toCurrencyCompact(data.pledged_amount),
              }}
            />
            <MetricsCard
              data={{
                label: __('Total Pledges', 'growfund'),
                amount: data.total_pledges.toString(),
              }}
            />
            <MetricsCard
              data={{
                label: __('Backed Amount', 'growfund'),
                amount: toCurrencyCompact(data.backed_amount),
              }}
            />
            <MetricsCard
              data={{
                label: __('Backed Campaigns', 'growfund'),
                amount: data.backed_campaigns.toString(),
              }}
            />
          </div>
        </div>
      );
    },
  });
};

export default BackerGivingStats;
