import { __ } from '@wordpress/i18n';
import { FileText } from 'lucide-react';
import { useCallback, useMemo } from 'react';
import { useFormContext, useWatch } from 'react-hook-form';
import { useNavigate } from 'react-router';

import { EmptySearchIcon2 } from '@/app/icons';
import { EmptyState, EmptyStateDescription } from '@/components/empty-state';
import { LoadingSkeleton } from '@/components/layouts/loading-skeleton';
import { Box, BoxContent, BoxTitle } from '@/components/ui/box';
import { Button } from '@/components/ui/button';
import { Progress } from '@/components/ui/progress';
import { RouteConfig } from '@/config/route-config';
import {
    type AnalyticsFilter,
    AnalyticType,
    type TopFunds,
} from '@/features/analytics/schemas/analytics';
import { useAnalyticsQuery } from '@/features/analytics/services/analytics';
import { useCurrency } from '@/hooks/use-currency';
import { useDebounce } from '@/hooks/use-debounce';
import { toQueryParamSafe } from '@/lib/date';

const TopFundsByRevenue = () => {
  const { toCurrency } = useCurrency();
  const navigate = useNavigate();
  const form = useFormContext<AnalyticsFilter>();

  const dateRange = useDebounce(useWatch({ control: form.control, name: 'date_range' }));

  const topFunds = useAnalyticsQuery<TopFunds[]>(AnalyticType.TopFunds, {
    start_date: dateRange?.from ? toQueryParamSafe(dateRange.from) : undefined,
    end_date: dateRange?.to ? toQueryParamSafe(dateRange.to) : undefined,
  });

  const funds = useMemo(() => {
    if (!topFunds.data) return [];

    return topFunds.data;
  }, [topFunds.data]);

  const highestRaisedFund = useMemo(() => {
    if (!topFunds.data) return 0;

    return Math.max(...topFunds.data.map((fund) => fund.fund_raised));
  }, [topFunds.data]);

  const progressPercentage = useCallback(
    (raised_amount: number) => {
      return (raised_amount / highestRaisedFund) * 100;
    },
    [highestRaisedFund],
  );

  if (funds.length === 0) {
    return (
      <Box className="growfund-rounded-3xl">
        <BoxContent className="growfund-px-6 growfund-py-4 growfund-h-full growfund-overflow-hidden">
          <BoxTitle>{__('Top Funds by Revenue', 'growfund')}</BoxTitle>
          <EmptyState className="growfund-h-full growfund-shadow-none growfund-pt-0">
            <EmptySearchIcon2 />
            <EmptyStateDescription>{__('No data found.', 'growfund')}</EmptyStateDescription>
          </EmptyState>
        </BoxContent>
      </Box>
    );
  }

  return (
    <Box className="growfund-rounded-3xl">
      <BoxContent className="growfund-px-6 growfund-py-4">
        <BoxTitle className="growfund-justify-between">
          <span>{__('Top Funds by Revenue', 'growfund')}</span>
          <Button
            variant="ghost"
            size="sm"
            className="growfund-opacity-0 group-hover/box:growfund-opacity-100"
            onClick={() => {
              void navigate(RouteConfig.Funds.buildLink());
            }}
          >
            <FileText className="growfund-size-4" />
            {__('See All Funds', 'growfund')}
          </Button>
        </BoxTitle>
        <div className="growfund-space-y-1 growfund-mt-4">
          <LoadingSkeleton
            loading={topFunds.isFetching || topFunds.isLoading}
            className="growfund-w-full"
            skeletonClassName="growfund-w-56"
          >
            {funds.map((fund, index) => {
              return (
                <div key={index} className=" growfund-w-full growfund-space-y-0.5">
                  <div className="growfund-flex growfund-items-center growfund-justify-between growfund-gap-8">
                    <div className="growfund-typo-tiny growfund-break-all growfund-line-clamp-1">
                      {fund.fund_title}
                    </div>
                    <div className="growfund-typo-tiny">{toCurrency(fund.fund_raised)}</div>
                  </div>
                  <Progress
                    value={progressPercentage(fund.fund_raised)}
                    size="sm"
                    className="growfund-h-0.5 growfund-bg-background-fill-caution"
                  />
                </div>
              );
            })}
          </LoadingSkeleton>
        </div>
      </BoxContent>
    </Box>
  );
};

export default TopFundsByRevenue;
