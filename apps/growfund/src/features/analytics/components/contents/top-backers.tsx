import { useMemo } from 'react';
import { useFormContext, useWatch } from 'react-hook-form';

import TopContributors from '@/features/analytics/components/shared/top-contributors';
import { type AnalyticsFilter, AnalyticType } from '@/features/analytics/schemas/analytics';
import { useAnalyticsQuery } from '@/features/analytics/services/analytics';
import { type Backer } from '@/features/backers/schemas/backer';
import { useDebounce } from '@/hooks/use-debounce';
import { toQueryParamSafe } from '@/lib/date';

const TopBackers = () => {
  const form = useFormContext<AnalyticsFilter>();

  const dateRange = useDebounce(useWatch({ control: form.control, name: 'date_range' }));

  const topBackersQuery = useAnalyticsQuery<Backer[]>(AnalyticType.TopBackers, {
    start_date: dateRange?.from ? toQueryParamSafe(dateRange.from) : undefined,
    end_date: dateRange?.to ? toQueryParamSafe(dateRange.to) : undefined,
  });

  const topBackers = useMemo(() => {
    if (!topBackersQuery.data) return [];
    return topBackersQuery.data;
  }, [topBackersQuery.data]);

  return <TopContributors users={topBackers} />;
};

export default TopBackers;
