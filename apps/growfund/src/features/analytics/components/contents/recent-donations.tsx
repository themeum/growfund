import { __ } from '@wordpress/i18n';
import { useMemo } from 'react';
import { useFormContext, useWatch } from 'react-hook-form';

import RecentContributions from '@/features/analytics/components/shared/recent-contributions';
import { type AnalyticsFilter, AnalyticType } from '@/features/analytics/schemas/analytics';
import { useAnalyticsQuery } from '@/features/analytics/services/analytics';
import { type Donation } from '@/features/donations/schemas/donation';
import { useDebounce } from '@/hooks/use-debounce';
import { toQueryParamSafe } from '@/lib/date';

const RecentDonations = () => {
  const form = useFormContext<AnalyticsFilter>();

  const dateRange = useDebounce(useWatch({ control: form.control, name: 'date_range' }));

  const recentDonationsQuery = useAnalyticsQuery<Donation[]>(AnalyticType.RecentContributions, {
    start_date: dateRange?.from ? toQueryParamSafe(dateRange.from) : undefined,
    end_date: dateRange?.to ? toQueryParamSafe(dateRange.to) : undefined,
  });

  const recentDonations = useMemo(() => {
    if (!recentDonationsQuery.data) return [];

    return recentDonationsQuery.data.map((donation) => ({
      id: donation.id,
      amount: donation.amount,
      status: donation.status,
      campaign_title: donation.campaign.title,
      contributor: {
        first_name: donation.donor.first_name,
        last_name: donation.donor.last_name,
        image: donation.donor.image,
      },
    }));
  }, [recentDonationsQuery.data]);

  return (
    <RecentContributions
      contributions={recentDonations}
      title={__('Recent Donations', 'growfund')}
      loading={recentDonationsQuery.isFetching || recentDonationsQuery.isLoading}
    />
  );
};

export default RecentDonations;
