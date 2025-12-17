import { zodResolver } from '@hookform/resolvers/zod';
import { __ } from '@wordpress/i18n';
import { parseAsString } from 'nuqs';
import { useForm } from 'react-hook-form';

import ElementWrapper from '@/components/element-wrapper';
import { DatePickerField } from '@/components/form/date-picker-field';
import RevenueBreakdownFallback from '@/components/pro-fallbacks/analytics/revenue-breakdown-fallback';
import { Form } from '@/components/ui/form';
import AnalyticsRevenueChart from '@/features/analytics/components/contents/analytics-revenue-chart';
import BackerOverTimeChart from '@/features/analytics/components/contents/backer-over-time-chart';
import RecentPledges from '@/features/analytics/components/contents/recent-pledges';
import TopBackers from '@/features/analytics/components/contents/top-backers';
import InformationMetrics from '@/features/analytics/components/shared/information-metrics';
import TopCampaigns from '@/features/analytics/components/shared/top-campaigns';
import {
  type AnalyticsFilter,
  AnalyticsFilterSchema,
} from '@/features/analytics/schemas/analytics';
import { CampaignIdProvider } from '@/features/campaigns/contexts/campaignId-context';
import { useFormQuerySync } from '@/hooks/use-form-query-sync';
import { toQueryParamSafe } from '@/lib/date';
import { registry } from '@/lib/registry';

const RewardModeAnalytics = () => {
  const form = useForm<AnalyticsFilter>({
    resolver: zodResolver(AnalyticsFilterSchema),
  });

  useFormQuerySync({
    keyMap: {
      start_date: parseAsString,
      end_date: parseAsString,
    },
    form,
    paramsToForm: (params) => ({
      date_range: {
        from: params.start_date ? new Date(params.start_date) : null,
        to: params.end_date ? new Date(params.end_date) : null,
      },
    }),
    formToParams: (formData) => ({
      start_date: formData.date_range?.from ? toQueryParamSafe(formData.date_range.from) : null,
      end_date: formData.date_range?.to ? toQueryParamSafe(formData.date_range.to) : null,
    }),
    watchFields: ['date_range'],
  });

  const AnalyticsRevenueBreakdownTable = registry.get('AnalyticsRevenueBreakdownTable');

  return (
    <CampaignIdProvider>
      <Form {...form}>
        <div className="growfund-flex growfund-items-center growfund-justify-between">
          <h4 className="growfund-typo-h4 growfund-font-semibold growfund-text-fg-primary">
            {__('Overview', 'growfund')}
          </h4>
          <div>
            <DatePickerField
              control={form.control}
              name="date_range"
              placeholder={__('Date: Last 30 days', 'growfund')}
              type="range"
              showRangePresets
              clearable
            />
          </div>
        </div>

        <div className="growfund-mt-4 growfund-space-y-7">
          <InformationMetrics />
          <AnalyticsRevenueChart />
          <div className="growfund-grid growfund-grid-cols-[auto_25rem] growfund-gap-7">
            <TopCampaigns />
            <BackerOverTimeChart />
          </div>
          <div className="growfund-grid growfund-grid-cols-[23.75rem_auto] growfund-gap-7">
            <TopBackers />
            <RecentPledges />
          </div>

          <ElementWrapper fallback={<RevenueBreakdownFallback />}>
            {AnalyticsRevenueBreakdownTable && <AnalyticsRevenueBreakdownTable />}
          </ElementWrapper>
        </div>
      </Form>
    </CampaignIdProvider>
  );
};

export default RewardModeAnalytics;
