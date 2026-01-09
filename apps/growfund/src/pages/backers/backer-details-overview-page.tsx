import { __ } from '@wordpress/i18n';

import UserPreviewCard from '@/components/users/user-preview-card';
import BackerActivityLogCard from '@/features/backers/components/backer-activity-log-card';
import { useBackerContext } from '@/features/backers/contexts/backer';
import { MetricsCard } from '@/features/overview/components/ui/metrics-card';
import { useCurrency } from '@/hooks/use-currency';

const BackerDetailsOverviewPage = () => {
  const { backer } = useBackerContext();
  const { toCurrencyCompact } = useCurrency();
  return (
    <div className="growfund-space-y-4 growfund-mt-4">
      <div className="growfund-grid growfund-gap-4">
        <div className="growfund-flex growfund-items-center growfund-gap-4">
          <MetricsCard
            data={{
              label: __('Pledged Amount', 'growfund'),
              amount: toCurrencyCompact(backer.pledged_amount),
            }}
          />

          <MetricsCard
            data={{
              label: __('Backed Amount', 'growfund'),
              amount: toCurrencyCompact(backer.backed_amount),
            }}
          />
          <MetricsCard
            data={{
              label: __('Pledged Campaigns', 'growfund'),
              amount: backer.pledged_campaigns.toString(),
            }}
          />
          <MetricsCard
            data={{
              label: __('Backed Campaigns', 'growfund'),
              amount: backer.backed_campaigns.toString(),
            }}
          />
        </div>
      </div>

      <div className="growfund-grid growfund-grid-cols-[20rem_auto] growfund-gap-4">
        <UserPreviewCard user={backer.backer_information} hideHeader />
        <BackerActivityLogCard />
      </div>
    </div>
  );
};

export default BackerDetailsOverviewPage;
