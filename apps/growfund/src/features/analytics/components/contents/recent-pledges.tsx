import { __ } from '@wordpress/i18n';
import { useMemo } from 'react';
import { useFormContext, useWatch } from 'react-hook-form';

import RecentContributions from '@/features/analytics/components/shared/recent-contributions';
import { type AnalyticsFilter, AnalyticType } from '@/features/analytics/schemas/analytics';
import { useAnalyticsQuery } from '@/features/analytics/services/analytics';
import { type Pledge } from '@/features/pledges/schemas/pledge';
import { useDebounce } from '@/hooks/use-debounce';
import { toQueryParamSafe } from '@/lib/date';

const RecentPledges = () => {
  const form = useFormContext<AnalyticsFilter>();

  const dateRange = useDebounce(useWatch({ control: form.control, name: 'date_range' }));

  const recentPledgesQuery = useAnalyticsQuery<Pledge[]>(AnalyticType.RecentContributions, {
    start_date: dateRange?.from ? toQueryParamSafe(dateRange.from) : undefined,
    end_date: dateRange?.to ? toQueryParamSafe(dateRange.to) : undefined,
  });

  const recentPledges = useMemo(() => {
    if (!recentPledgesQuery.data) return [];

    return recentPledgesQuery.data.map((pledge) => ({
      id: pledge.id,
      status: pledge.status,
      amount: (pledge.payment.amount ?? 0) + (pledge.payment.bonus_support_amount ?? 0),
      campaign_title: pledge.campaign.title,
      contributor: {
        first_name: pledge.backer.first_name,
        last_name: pledge.backer.last_name,
        image: pledge.backer.image,
      },
    }));
  }, [recentPledgesQuery.data]);

  return (
    <RecentContributions
      contributions={recentPledges}
      title={__('Recent Pledges', 'growfund')}
      loading={recentPledgesQuery.isFetching || recentPledgesQuery.isLoading}
    />
  );
};

export default RecentPledges;
