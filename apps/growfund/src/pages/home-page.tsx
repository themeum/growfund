import { zodResolver } from '@hookform/resolvers/zod';
import { __ } from '@wordpress/i18n';
import { Shuffle } from 'lucide-react';
import { parseAsString } from 'nuqs';
import { useForm } from 'react-hook-form';
import { useNavigate } from 'react-router';

import { DatePickerField } from '@/components/form/date-picker-field';
import { Container } from '@/components/layouts/container';
import { Page, PageContent, PageHeader, PageSubHeader } from '@/components/layouts/page';
import { Button } from '@/components/ui/button';
import { Form } from '@/components/ui/form';
import { Image } from '@/components/ui/image';
import { growfundConfig } from '@/config/growfund';
import { useAppConfig } from '@/contexts/app-config';
import {
    type AnalyticsFilter,
    AnalyticsFilterSchema,
} from '@/features/analytics/schemas/analytics';
import { CampaignIdProvider } from '@/features/campaigns/contexts/campaignId-context';
import DonationModeOverview from '@/features/overview/components/donation-mode-overview';
import RewardModeOverview from '@/features/overview/components/reward-mode-overview';
import { useFormQuerySync } from '@/hooks/use-form-query-sync';
import { toQueryParamSafe } from '@/lib/date';

const HomePage = () => {
  const { isDonationMode } = useAppConfig();
  const navigate = useNavigate();

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

  return (
    <Page>
      <PageHeader name={__('Home', 'growfund')} />
      <PageContent>
        <Container className="growfund-my-6">
          {growfundConfig.is_migration_available_from_crowdfunding && (
            <div className="growfund-grid growfund-grid-cols-3 growfund-h-28 growfund-mb-6 growfund-rounded-md">
              <Image
                src="/images/migration-top-banner.webp"
                fit="cover"
                className="growfund-border-none growfund-bg-transparent growfund-size-full growfund-rounded-none growfund-rounded-l-md"
              />
              <div className="growfund-col-span-2 growfund-flex growfund-items-center growfund-justify-between growfund-w-full growfund-border growfund-border-border growfund-border-l-0 growfund-py-5 growfund-px-12 growfund-bg-white growfund-rounded-r-md">
                <div className="growfund-flex growfund-flex-col growfund-gap-1 growfund-w-80">
                  <div className="growfund-typo-h6 growfund-text-fg-primary">
                    {__('Migrate to Growfund', 'growfund')}
                  </div>
                  <div className="growfund-typo-small growfund-text-fg-secondary">
                    {__(
                      'Move to Growfund for the best crowdfunding experience in WordPress.',
                      'growfund',
                    )}
                  </div>
                </div>
                <div className="growfund-flex growfund-items-center growfund-gap-3">
                  <Button
                    variant="primary"
                    onClick={(event) => {
                      event.preventDefault();
                      void navigate('/migrate-from-crowdfunding');
                    }}
                  >
                    <Shuffle />
                    {__('Migrate Now', 'growfund')}
                  </Button>
                </div>
              </div>
            </div>
          )}
          <Form {...form}>
            <PageSubHeader
              title={__('Overview', 'growfund')}
              action={
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
              }
            />
            <CampaignIdProvider>
              {isDonationMode ? <DonationModeOverview /> : <RewardModeOverview />}
            </CampaignIdProvider>
          </Form>
        </Container>
      </PageContent>
    </Page>
  );
};

export default HomePage;
