import { __ } from '@wordpress/i18n';
import { useMemo } from 'react';
import { useFormContext, useWatch } from 'react-hook-form';

import { EmptySearchIcon2 } from '@/app/icons';
import StackedAreaChart from '@/components/charts/stacked-area-chart';
import { EmptyState, EmptyStateDescription } from '@/components/empty-state';
import { Box, BoxContent, BoxTitle } from '@/components/ui/box';
import { type AnalyticsFilter, AnalyticType } from '@/features/analytics/schemas/analytics';
import { useAnalyticsQuery } from '@/features/analytics/services/analytics';
import { useDebounce } from '@/hooks/use-debounce';
import { toQueryParamSafe } from '@/lib/date';

const BackerOverTimeChart = () => {
  const form = useFormContext<AnalyticsFilter>();

  const dateRange = useDebounce(useWatch({ control: form.control, name: 'date_range' }));

  const backerOverTimesQuery = useAnalyticsQuery<
    {
      date: string;
      first_time_total: number;
      recurring_total: number;
    }[]
  >(AnalyticType.BackerOverTime, {
    start_date: dateRange?.from ? toQueryParamSafe(dateRange.from) : undefined,
    end_date: dateRange?.to ? toQueryParamSafe(dateRange.to) : undefined,
  });

  const backerOverTimes = useMemo(() => {
    if (!backerOverTimesQuery.data) return [];

    return backerOverTimesQuery.data;
  }, [backerOverTimesQuery.data]);

  if (backerOverTimes.length === 0) {
    return (
      <Box className="growfund-rounded-3xl">
        <BoxContent className="growfund-px-6 growfund-py-4 growfund-h-full growfund-overflow-hidden">
          <BoxTitle>{__('Backer Over Time', 'growfund')}</BoxTitle>
          <EmptyState className="growfund-h-full growfund-shadow-none growfund-pt-0">
            <EmptySearchIcon2 />
            <EmptyStateDescription>{__('No data found.', 'growfund')}</EmptyStateDescription>
          </EmptyState>
        </BoxContent>
      </Box>
    );
  }
  return (
    <>
      <Box className="growfund-rounded-3xl">
        <BoxContent className="growfund-py-4 growfund-h-full">
          <BoxTitle className="growfund-px-6">
            <span>{__('Backer Over Time', 'growfund')}</span>
          </BoxTitle>
          <div className="growfund-py-8 growfund-h-full">
            <StackedAreaChart labelName="date" data={backerOverTimes} />
          </div>
        </BoxContent>
      </Box>
    </>
  );
};

export default BackerOverTimeChart;
