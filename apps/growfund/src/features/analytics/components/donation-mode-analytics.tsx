import { zodResolver } from '@hookform/resolvers/zod';
import { __ } from '@wordpress/i18n';
import { parseAsString } from 'nuqs';
import { useForm } from 'react-hook-form';

import ElementWrapper from '@/components/element-wrapper';
import { DatePickerField } from '@/components/form/date-picker-field';
import RevenueBreakdownFallback from '@/components/pro-fallbacks/analytics/revenue-breakdown-fallback';
import { Form } from '@/components/ui/form';
import { useAppConfig } from '@/contexts/app-config';
import AnalyticsRevenueChart from '@/features/analytics/components/contents/analytics-revenue-chart';
import DonorOverTimeChart from '@/features/analytics/components/contents/donor-over-time-chart';
import RecentDonations from '@/features/analytics/components/contents/recent-donations';
import TopDonors from '@/features/analytics/components/contents/top-donors';
import TopFundsByRevenue from '@/features/analytics/components/contents/top-funds-by-revenue';
import InformationMetrics from '@/features/analytics/components/shared/information-metrics';
import TopCampaigns from '@/features/analytics/components/shared/top-campaigns';
import {
  type AnalyticsFilter,
  AnalyticsFilterSchema,
} from '@/features/analytics/schemas/analytics';
import { CampaignIdProvider } from '@/features/campaigns/contexts/campaignId-context';
import { AppConfigKeys } from '@/features/settings/context/settings-context';
import { useFormQuerySync } from '@/hooks/use-form-query-sync';
import { toQueryParamSafe } from '@/lib/date';
import { registry } from '@/lib/registry';
import { cn } from '@/lib/utils';

const DonationAnalytics = () => {
  const { appConfig } = useAppConfig();

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
            <DonorOverTimeChart />
          </div>
          <div className="growfund-grid growfund-grid-cols-2 growfund-gap-7">
            <TopDonors />
            <RecentDonations />
          </div>
          <div
            className={cn(
              'growfund-grid growfund-gap-7',
              appConfig[AppConfigKeys.Campaign]?.allow_fund && 'growfund-grid-cols-[38rem_auto]',
            )}
          >
            <ElementWrapper fallback={<RevenueBreakdownFallback />}>
              {AnalyticsRevenueBreakdownTable && <AnalyticsRevenueBreakdownTable />}
            </ElementWrapper>
            {appConfig[AppConfigKeys.Campaign]?.allow_fund && <TopFundsByRevenue />}
          </div>
        </div>
      </Form>
    </CampaignIdProvider>
  );
};

export default DonationAnalytics;
