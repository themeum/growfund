import { useMemo } from 'react';
import { useFormContext, useWatch } from 'react-hook-form';

import TopContributors from '@/features/analytics/components/shared/top-contributors';
import { type AnalyticsFilter, AnalyticType } from '@/features/analytics/schemas/analytics';
import { useAnalyticsQuery } from '@/features/analytics/services/analytics';
import { type Donor } from '@/features/donors/schemas/donor';
import { useDebounce } from '@/hooks/use-debounce';
import { toQueryParamSafe } from '@/lib/date';

const TopDonors = () => {
  const form = useFormContext<AnalyticsFilter>();

  const dateRange = useDebounce(useWatch({ control: form.control, name: 'date_range' }));

  const topDonorsQuery = useAnalyticsQuery<Donor[]>(AnalyticType.TopDonors, {
    start_date: dateRange?.from ? toQueryParamSafe(dateRange.from) : undefined,
    end_date: dateRange?.to ? toQueryParamSafe(dateRange.to) : undefined,
  });

  const topDonors = useMemo(() => {
    if (!topDonorsQuery.data) return [];

    return topDonorsQuery.data.map((donor) => {
      return {
        id: donor.id,
        first_name: donor.first_name,
        last_name: donor.last_name,
        image: donor.image,
        total_contributions: donor.total_contributions,
        number_of_contributions: donor.number_of_contributions,
      };
    });
  }, [topDonorsQuery.data]);

  return <TopContributors users={topDonors} />;
};

export default TopDonors;
