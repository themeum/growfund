import { __ } from '@wordpress/i18n';

import { Container } from '@/components/layouts/container';
import { Page, PageContent, PageHeader } from '@/components/layouts/page';
import { useAppConfig } from '@/contexts/app-config';
import DonationModeAnalytics from '@/features/analytics/components/donation-mode-analytics';
import RewardModeAnalytics from '@/features/analytics/components/reward-mode-analytics';

const AnalyticsPage = () => {
  const { isDonationMode } = useAppConfig();
  return (
    <Page>
      <PageHeader name={__('Analytics', 'growfund')} />
      <PageContent>
        <Container className="growfund-mt-6">
          {isDonationMode ? <DonationModeAnalytics /> : <RewardModeAnalytics />}
        </Container>
      </PageContent>
    </Page>
  );
};

export default AnalyticsPage;
